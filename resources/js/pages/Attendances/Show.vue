<!-- resources/js/Pages/Attendances/Show.vue -->
<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Calendar, Users } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps({
    classroom: Object,
    date: String, // Format: YYYY-MM-DD
    students: Array,
    attendances: Object, // Associative array: { [student_id]: attendance_data }
    activeAcademicYear: Object,
});

const selectedDate = ref(props.date);

// --- State untuk form absensi ---
const attendanceData = ref({});

// --- Inisialisasi data absensi dari props ---
const initializeAttendanceData = () => {
    const initialData = {};
    props.students.forEach((student) => {
        const existing = props.attendances[student.id];
        initialData[student.id] = {
            student_id: student.id,
            status: existing?.status || 'present',
            is_late: existing?.is_late || false,
            late_time: existing?.late_time || null,
            notes: existing?.notes || '',
        };
    });
    attendanceData.value = initialData;
};

// --- Panggil inisialisasi saat komponen dimuat dan saat props berubah ---
initializeAttendanceData();
watch(() => [props.attendances, props.students], initializeAttendanceData, { deep: true });

// --- Fungsi untuk mengubah tanggal ---
const changeDate = () => {
    router.get(route('attendances.show', { classroom: props.classroom.id }), { date: selectedDate.value });
};

// --- Fungsi untuk mengupdate data absen siswa ---
const updateAttendance = (studentId, field, value) => {
    if (!attendanceData.value[studentId]) {
        attendanceData.value[studentId] = {
            student_id: studentId,
            status: 'present',
            is_late: false,
            late_time: null,
            notes: '',
        };
    }
    attendanceData.value[studentId][field] = value;

    // Logika tambahan: jika is_late di-uncheck, hapus late_time
    if (field === 'is_late' && !value) {
        attendanceData.value[studentId]['late_time'] = null;
    }
};

// --- Fungsi untuk menyimpan absensi ---
const saveAttendance = () => {
    const dataArray = Object.values(attendanceData.value);

    router.post(
        route('attendances.store', { classroom: props.classroom.id }),
        {
            date: selectedDate.value,
            attendance_data: dataArray,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Absensi berhasil disimpan');
            },
            onError: (errors) => {
                console.error('Error validasi:', errors);
            },
        },
    );
};

// --- Computed untuk mendapatkan warna baris berdasarkan status ---
const getRowClass = (studentId) => {
    const status = attendanceData.value[studentId]?.status || 'present';
    switch (status) {
        case 'sick':
            return 'bg-blue-100 hover:bg-blue-200 dark:text-black'; // Biru muda untuk Sakit
        case 'leave':
            return 'bg-yellow-100 hover:bg-yellow-200 dark:text-black'; // Kuning muda untuk Izin
        case 'absent':
            return 'bg-red-100 hover:bg-red-200 dark:text-black'; // Merah muda untuk Alpha
        case 'present':
        default:
            return 'bg-background hover:bg-secondary'; // Putih untuk Hadir (default)
    }
};
</script>

<template>
    <AppLayout :title="`Absensi Kelas ${classroom.name}`">
        <div class="space-y-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold md:text-3xl">Absensi Kelas {{ classroom.name }}</h1>
                <p class="text-muted-foreground">Tanggal: {{ date }}</p>
            </div>

            <!-- Pilih Tanggal -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <Calendar class="mr-2 h-5 w-5" />
                        Pilih Tanggal
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-end space-x-2">
                        <div class="grid w-full max-w-sm items-center gap-1.5">
                            <Label for="attendance_date">Tanggal Absen</Label>
                            <Input id="attendance_date" v-model="selectedDate" type="date" />
                        </div>
                        <Button @click="changeDate">Muat Absensi</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Tabel Absensi -->
            <form @submit.prevent="saveAttendance">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Users class="mr-2 h-5 w-5" />
                            Daftar Siswa
                        </CardTitle>
                        <p class="text-sm text-muted-foreground">Isi status kehadiran siswa untuk tanggal {{ date }}.</p>
                        <!-- Petunjuk Keterlambatan -->
                        <p class="text-xs text-muted-foreground italic">
                            *Catatan: Keterlambatan dicatat pada pagi hari saat siswa datang ke sekolah.
                        </p>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[100px]">NIS</TableHead>
                                    <TableHead>Nama</TableHead>
                                    <TableHead class="w-[150px]">Status</TableHead>
                                    <TableHead class="w-[100px] text-center">Terlambat</TableHead>
                                    <TableHead class="w-[120px]">Jam Datang</TableHead>
                                    <TableHead>Keterangan</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="student in students" :key="student.id" :class="getRowClass(student.id)">
                                    <TableCell class="font-medium">{{ student.student_id }}</TableCell>
                                    <TableCell>{{ student.name }}</TableCell>
                                    <TableCell>
                                        <Select v-model="attendanceData[student.id].status">
                                            <SelectTrigger class="w-full">
                                                <SelectValue placeholder="Pilih Status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="present">Hadir</SelectItem>
                                                <SelectItem value="sick">Sakit</SelectItem>
                                                <SelectItem value="leave">Izin</SelectItem>
                                                <SelectItem value="absent">Alpha</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <input
                                            type="checkbox"
                                            :checked="attendanceData[student.id]?.is_late"
                                            @change="updateAttendance(student.id, 'is_late', $event.target.checked)"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <!-- Input time sudah menggunakan format 24-jam -->
                                        <Input
                                            v-if="attendanceData[student.id]?.is_late"
                                            v-model="attendanceData[student.id].late_time"
                                            type="time"
                                            step="60"
                                            class="w-full"
                                        />
                                        <!-- Petunjuk kecil jika diperlukan -->
                                        <!-- <p v-if="attendanceData[student.id]?.is_late" class="mt-1 text-xs text-muted-foreground">Format 24-jam (HH:MM)</p> -->
                                    </TableCell>
                                    <TableCell>
                                        <Input v-model="attendanceData[student.id].notes" type="text" placeholder="Opsional..." class="w-full" />
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <!-- Tombol Simpan -->
                <div class="mt-6 flex justify-end">
                    <Button type="submit">Simpan Absensi</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
