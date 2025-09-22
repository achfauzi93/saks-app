<!-- resources/js/Pages/StudentViolations/StudentViolationModal.vue -->
<script setup>
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { defineEmits, defineProps, ref, watch } from 'vue';

const props = defineProps({
    // --- Props Umum ---
    isOpen: {
        type: Boolean,
        required: true,
    },
    mode: {
        // Prop baru untuk menentukan mode
        type: String,
        default: 'create', // 'create' atau 'edit'
        validator: (value) => ['create', 'edit'].includes(value),
    },
    // --- Props Data ---
    violationTypes: {
        type: Array,
        required: true,
    },
    counselors: {
        type: Array,
        default: () => [],
    },
    // --- Props untuk Mode Edit ---
    initialData: {
        // Data pelanggaran yang akan diedit
        type: Object,
        default: null,
    },
    // Data tambahan untuk mode edit jika diperlukan (misalnya daftar siswa)
    // Kita bisa mengirimkannya, atau fetch ulang. Untuk kesederhanaan, kita fetch ulang.
});

const emit = defineEmits(['update:isOpen', 'submit']);

// --- State untuk Dropdown ---
const classrooms = ref([]);
const selectedClassroom = ref('');
const studentsInClassroom = ref([]);
const isLoadingStudents = ref(false);

// --- Form Inertia ---
const form = useForm({
    student_id: '',
    violation_type_id: '',
    violation_date: new Date().toISOString().split('T')[0],
    notes: '',
    counselor_id: '',
    follow_up: '',
});

// Fungsi pembantu untuk memformat tanggal dari API ke format input date
const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    // Jika dateString sudah dalam format YYYY-MM-DD, kembalikan apa adanya
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
        return dateString;
    }
    // Jika dateString adalah ISO string (2024-06-18T00:00:00.000000Z), ekstrak tanggalnya
    const date = new Date(dateString);
    if (isNaN(date)) return ''; // Jika tidak valid, kembalikan string kosong

    const year = date.getFullYear();
    // Pastikan bulan dan tanggal dua digit
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

// --- Fetch Data untuk Dropdown ---
const fetchClassrooms = async () => {
    try {
        const response = await axios.get(
            route('classrooms.index', {
                academic_year_id: 'active',
                only_classrooms: true,
            }),
        );
        classrooms.value = response.data.classrooms || [];
    } catch (error) {
        console.error('Gagal fetch kelas:', error);
        classrooms.value = [];
    }
};

const fetchStudentsByClassroom = async () => {
    if (!selectedClassroom.value) {
        studentsInClassroom.value = [];
        // Jangan reset form.student_id di sini jika sedang edit dan kelas belum dipilih
        return;
    }

    isLoadingStudents.value = true;

    try {
        const response = await axios.get(route('student-violations.students-by-classroom'), {
            params: {
                classroom_id: selectedClassroom.value,
            },
        });

        studentsInClassroom.value = response.data;
        // Hanya reset student_id jika tidak dalam mode edit atau jika kelas berubah
        // di mode edit, student_id akan diisi oleh watch initialData
        if (props.mode !== 'edit') {
            form.student_id = '';
        }
    } catch (error) {
        console.error('Gagal fetch siswa:', error);
        studentsInClassroom.value = [];
    } finally {
        isLoadingStudents.value = false;
    }
};

// --- Watchers ---
watch(selectedClassroom, fetchStudentsByClassroom);

// --- Watcher Utama: Saat Modal Dibuka atau Props Berubah ---
watch(
    [() => props.isOpen, () => props.initialData, () => props.mode],
    ([newIsOpen, newInitialData, newMode]) => {
        if (newIsOpen) {
            // 1. Fetch kelas
            fetchClassrooms();

            if (newMode === 'edit' && newInitialData) {
                // 2a. Mode Edit: Isi form dengan data awal
                form.reset(); // Reset dulu untuk menghapus error sebelumnya
                form.student_id = newInitialData.student_id || '';
                form.violation_type_id = newInitialData.violation_type_id || '';
                form.violation_date = formatDateForInput(newInitialData.violation_date);
                form.notes = newInitialData.notes || '';
                form.counselor_id = newInitialData.counselor_id || '';
                form.follow_up = newInitialData.follow_up || '';

                // Reset state dropdown
                selectedClassroom.value = newInitialData.classroom?.id || '';
                studentsInClassroom.value = []; // Akan diisi oleh fetchStudentsByClassroom jika kelas ada

                // 2b. Jika kelas awal diketahui, fetch siswanya
                if (selectedClassroom.value) {
                    // Tunda sedikit agar Select bisa dirender dulu
                    setTimeout(() => {
                        fetchStudentsByClassroom();
                    }, 100);
                }
            } else {
                // 2c. Mode Create: Reset form dan state
                form.reset();
                form.violation_date = new Date().toISOString().split('T')[0]; // Default ke hari ini
                selectedClassroom.value = '';
                studentsInClassroom.value = [];
            }
            form.clearErrors(); // Bersihkan error dari submit sebelumnya
        } else {
            // Saat modal ditutup
            form.clearErrors();
        }
    },
    { immediate: true },
);

// --- Submit Handler ---
const onSubmit = () => {
    if (props.mode === 'create') {
        form.post(route('student-violations.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submit');
                emit('update:isOpen', false);
            },
        });
    } else if (props.mode === 'edit' && props.initialData) {
        form.put(route('student-violations.update', props.initialData.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submit');
                emit('update:isOpen', false);
            },
        });
    }
};
</script>

<template>
    <Dialog :open="isOpen" @update:open="$emit('update:isOpen', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ mode === 'create' ? 'Tambah' : 'Edit' }} Catatan Pelanggaran</DialogTitle>
                <DialogDescription>
                    {{ mode === 'create' ? 'Pilih kelas, lalu pilih siswa yang melakukan pelanggaran.' : 'Perbarui informasi pelanggaran.' }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="onSubmit" class="grid gap-4 py-4">
                <!-- Kelas (Hanya untuk create) -->
                <div v-if="mode === 'create'" class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="classroom_id">Kelas *</Label>
                    <Select v-model="selectedClassroom">
                        <SelectTrigger id="classroom_id" class="w-full">
                            <SelectValue placeholder="Pilih Kelas" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                {{ classroom.name }} ({{ classroom.academic_year?.name }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Informasi Siswa (Read-only di mode edit) -->
                <div v-if="mode === 'edit' && initialData" class="grid w-full max-w-sm items-center gap-1.5">
                    <Label>Siswa</Label>
                    <div class="rounded-md border border-input bg-muted px-3 py-2 text-sm">
                        {{ initialData.student?.name }} ({{ initialData.student?.student_id }})
                    </div>
                </div>

                <!-- Siswa (Dropdown di mode create, hidden di mode edit) -->
                <div v-if="mode === 'create'" class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="student_id">Siswa *</Label>
                    <Select v-model="form.student_id" :disabled="!selectedClassroom || isLoadingStudents">
                        <SelectTrigger id="student_id" class="w-full">
                            <SelectValue placeholder="Pilih Siswa" />
                        </SelectTrigger>
                        <SelectContent>
                            <!-- Loading -->
                            <SelectItem v-if="isLoadingStudents" disabled value="__loading__">
                                <div class="flex items-center">
                                    <svg class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Memuat siswa...
                                </div>
                            </SelectItem>

                            <!-- Daftar siswa -->
                            <SelectItem v-for="student in studentsInClassroom" :key="student.id" :value="student.id">
                                {{ student.name }} ({{ student.student_id }})
                            </SelectItem>

                            <!-- Empty state -->
                            <SelectItem v-if="!isLoadingStudents && studentsInClassroom.length === 0 && selectedClassroom" disabled value="__empty__">
                                Tidak ada siswa di kelas ini
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.student_id" />
                </div>

                <!-- Jenis Pelanggaran -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="violation_type_id">Jenis Pelanggaran *</Label>
                    <Select v-model="form.violation_type_id">
                        <SelectTrigger id="violation_type_id" class="w-full">
                            <SelectValue placeholder="Pilih Jenis Pelanggaran" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="type in violationTypes" :key="type.id" :value="type.id">
                                {{ type.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.violation_type_id" />
                </div>

                <!-- Tanggal Kejadian -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="violation_date">Tanggal Kejadian *</Label>
                    <Input id="violation_date" type="date" v-model="form.violation_date" />
                    <InputError :message="form.errors.violation_date" />
                </div>

                <!-- Keterangan -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="notes">Keterangan</Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        placeholder="Opsional..."
                    ></textarea>
                    <InputError :message="form.errors.notes" />
                </div>

                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="follow_up">Tindak Lanjut</Label>
                    <textarea
                        id="follow_up"
                        v-model="form.follow_up"
                        rows="3"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        placeholder="Tindak lanjut dari kasus ini..."
                    ></textarea>
                </div>

                <!-- Guru BK -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="counselor_id">Guru BK</Label>
                    <Select v-model="form.counselor_id">
                        <SelectTrigger id="counselor_id" class="w-full">
                            <SelectValue placeholder="Pilih Guru BK" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="counselor in counselors" :key="counselor.id" :value="counselor.id">
                                {{ counselor.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Submit -->
                <DialogFooter>
                    <Button type="submit" :disabled="form.processing || (mode === 'create' && !form.student_id)">
                        <span v-if="form.processing">
                            {{ mode === 'create' ? 'Menyimpan...' : 'Memperbarui...' }}
                        </span>
                        <span v-else>
                            {{ mode === 'create' ? 'Simpan' : 'Perbarui' }}
                        </span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Tambahkan style khusus jika diperlukan */
</style>
