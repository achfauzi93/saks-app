<!-- resources/js/Pages/StudentClassAssignments/Index.vue -->
<script setup>
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ArrowRightLeft, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Modal
import AssignModal from './AssignModal.vue';
import TransferModal from './TransferModal.vue';

const props = defineProps({
    assignments: Object,
    filters: Object,
    classrooms: Array,
    students: Array,
});

const searchClassroom = ref('');
const searchStudent = ref('');
const statusFilter = ref(props.filters.status || '');

const selectedClassroom = ref(props.filters.classroom_id || '');
const selectedStudent = ref(props.filters.student_id || '');

const isAssignModalOpen = ref(false);
const isTransferModalOpen = ref(false);
const selectedStudentForTransfer = ref(null);

const filteredClassrooms = computed(() => {
    if (!searchClassroom.value) return props.classrooms;
    return props.classrooms.filter((c) => c.name.toLowerCase().includes(searchClassroom.value.toLowerCase()));
});

const filteredStudents = computed(() => {
    if (!searchStudent.value) return props.students;
    return props.students.filter(
        (s) => s.name.toLowerCase().includes(searchStudent.value.toLowerCase()) || s.student_id.includes(searchStudent.value),
    );
});

const submitFilter = () => {
    router.get(
        route('student-class-assignments.index'),
        {
            classroom_id: selectedClassroom.value,
            student_id: selectedStudent.value,
            status: statusFilter.value,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const clearFilter = () => {
    selectedClassroom.value = '';
    selectedStudent.value = '';
    statusFilter.value = '';
    submitFilter();
};

const openAssignModal = () => {
    isAssignModalOpen.value = true;
};

const openTransferModal = (studentId, studentName) => {
    selectedStudentForTransfer.value = { id: studentId, name: studentName };
    isTransferModalOpen.value = true;
};

const handleModalSubmit = () => {
    router.reload();
};
</script>

<template>
    <AppLayout title="Penugasan Siswa ke Kelas">
        <Card class="mx-auto w-full rounded-none border-0 border-b-0 shadow-none">
            <CardHeader class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <CardTitle>Penugasan Siswa ke Kelas</CardTitle>
                <Button @click="openAssignModal">
                    <Plus class="mr-2 h-4 w-4" />
                    Assign Siswa
                </Button>
            </CardHeader>
            <CardContent>
                <!-- Filters -->
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Filter Kelas</label>
                            <Input v-model="searchClassroom" placeholder="Cari kelas..." class="mb-1" />
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
                            <label class="mb-1 block text-sm font-medium">Filter Siswa</label>
                            <Input v-model="searchStudent" placeholder="Cari siswa..." class="mb-1" />
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
                            <label class="mb-1 block text-sm font-medium">Status</label>
                            <select
                                v-model="statusFilter"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                            >
                                <option value="">Semua</option>
                                <option value="active">Aktif</option>
                            </select>
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
                                <TableHead>Mulai</TableHead>
                                <TableHead>Akhir</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="item in assignments.data" :key="item.id">
                                <TableCell>
                                    {{ item.student?.name }}<br />
                                    <span class="text-xs text-muted-foreground">{{ item.student?.student_id }}</span>
                                </TableCell>
                                <TableCell>{{ item.classroom?.name || '-' }}</TableCell>
                                <TableCell>{{ item.start_date }}</TableCell>
                                <TableCell>{{ item.end_date || '-' }}</TableCell>
                                <TableCell>
                                    <span
                                        :class="item.end_date ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                        class="rounded-full px-2 py-0.5 text-xs"
                                    >
                                        {{ item.end_date ? 'Tidak Aktif' : 'Aktif' }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button
                                        v-if="!item.end_date"
                                        size="sm"
                                        variant="outline"
                                        @click="openTransferModal(item.student_id, item.student.name)"
                                    >
                                        <ArrowRightLeft class="h-3 w-3" />
                                        Pindah
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="assignments.data.length === 0">
                                <TableCell colspan="6" class="py-4 text-center"> Tidak ada data assignment. </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <Pagination
                        v-if="assignments.links"
                        :meta="assignments"
                        :links="assignments.links"
                        :query-params="{
                            classroom_id: selectedClassroom,
                            student_id: selectedStudent,
                            status: statusFilter,
                        }"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Modal Assign -->
        <AssignModal v-model:isOpen="isAssignModalOpen" :classrooms="classrooms" :students="students" @submit="handleModalSubmit" />

        <!-- Modal Transfer -->
        <TransferModal
            v-model:isOpen="isTransferModalOpen"
            :student="selectedStudentForTransfer"
            :classrooms="classrooms"
            @submit="handleModalSubmit"
        />
    </AppLayout>
</template>
