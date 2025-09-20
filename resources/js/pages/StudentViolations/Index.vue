<!-- resources/js/Pages/StudentViolations/Index.vue -->
<script setup>
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import StudentViolationModal from './StudentViolationModal.vue';

const props = defineProps({
    violations: Object,
    filters: Object,
    students: Array,
    classrooms: Array,
    violationTypes: Array,
    activeAcademicYear: Array,
});

// Filter states
const selectedStudent = ref(props.filters.student_id || '');
const selectedClassroom = ref(props.filters.classroom_id || '');
const selectedViolationType = ref(props.filters.violation_type_id || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

// Modal
const isCreateModalOpen = ref(false);

const filteredStudents = computed(() => {
    return props.students;
});

const filteredClassrooms = computed(() => {
    return props.classrooms;
});

const filteredViolationTypes = computed(() => {
    return props.violationTypes;
});

const submitFilter = () => {
    router.get(
        route('student-violations.index'),
        {
            student_id: selectedStudent.value,
            classroom_id: selectedClassroom.value,
            violation_type_id: selectedViolationType.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const clearFilter = () => {
    selectedStudent.value = '';
    selectedClassroom.value = '';
    selectedViolationType.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    submitFilter();
};

const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

const deleteViolation = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus catatan pelanggaran ini?')) {
        router.delete(route('student-violations.destroy', id));
    }
};

const handleModalSubmit = () => {
    router.reload();
};
</script>

<template>
    <AppLayout title="Catatan Pelanggaran Siswa">
        <Card class="mx-auto w-full rounded-none border-0 border-b-0 shadow-none">
            <CardHeader class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div v-if="!activeAcademicYear" class="mb-4 rounded-md bg-yellow-50 p-3 text-sm text-yellow-800">
                    ⚠️ Tidak ada tahun ajaran yang aktif. Silakan atur tahun ajaran aktif di menu
                    <Link :href="route('academic-years.index')" class="font-medium underline">Tahun Akademik</Link>
                    sebelum mencatat pelanggaran.
                </div>
                <CardTitle>Catatan Pelanggaran Siswa</CardTitle>
                <Button @click="openCreateModal">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Pelanggaran
                </Button>
            </CardHeader>
            <CardContent>
                <!-- Filters -->
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-5">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Siswa</label>
                            <select
                                v-model="selectedStudent"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                            >
                                <option value="">Semua Siswa</option>
                                <option v-for="student in filteredStudents" :key="student.id" :value="student.id">
                                    {{ student.name }} ({{ student.student_id }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Kelas</label>
                            <select
                                v-model="selectedClassroom"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                            >
                                <option value="">Semua Kelas</option>
                                <option v-for="classroom in filteredClassrooms" :key="classroom.id" :value="classroom.id">
                                    {{ classroom.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Jenis Pelanggaran</label>
                            <select
                                v-model="selectedViolationType"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                            >
                                <option value="">Semua Jenis</option>
                                <option v-for="type in filteredViolationTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Dari Tanggal</label>
                            <Input v-model="dateFrom" type="date" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Sampai Tanggal</label>
                            <Input v-model="dateTo" type="date" />
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <Button variant="outline" @click="submitFilter"> Terapkan </Button>
                        <Button variant="ghost" @click="clearFilter"> Reset </Button>
                    </div>
                </div>

                <!-- Table -->
                <div class="mt-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Siswa</TableHead>
                                <TableHead>Kelas</TableHead>
                                <TableHead>Wali Kelas</TableHead>
                                <TableHead>Jenis Pelanggaran</TableHead>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>Keterangan</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="item in violations.data" :key="item.id">
                                <TableCell>
                                    {{ item.student?.name }}<br />
                                    <span class="text-xs text-muted-foreground">{{ item.student?.student_id }}</span>
                                </TableCell>
                                <TableCell>{{ item.classroom?.name || '-' }}</TableCell>
                                <TableCell>{{ item.homeroom_teacher?.name || '-' }}</TableCell>
                                <TableCell>{{ item.violation_type?.name || '-' }}</TableCell>
                                <TableCell>{{ item.violation_date }}</TableCell>
                                <TableCell>{{ item.notes || '-' }}</TableCell>
                                <TableCell class="text-right">
                                    <Button size="sm" variant="destructive" @click="deleteViolation(item.id)">
                                        <Trash2 class="h-3 w-3" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="violations.data.length === 0">
                                <TableCell colspan="7" class="py-4 text-center"> Tidak ada data pelanggaran. </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <Pagination
                        v-if="violations.links"
                        :meta="violations"
                        :links="violations.links"
                        :query-params="{
                            student_id: selectedStudent,
                            classroom_id: selectedClassroom,
                            violation_type_id: selectedViolationType,
                            date_from: dateFrom,
                            date_to: dateTo,
                        }"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Modal Create -->
        <StudentViolationModal
            v-model:isOpen="isCreateModalOpen"
            :students="students"
            :violation-types="violationTypes"
            @submit="handleModalSubmit"
        />
    </AppLayout>
</template>
