<script setup>
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

// Komponen pendukung
import ActionButtons from '@/components/AcademicYear/ActionButtons.vue';
import StatusBadge from '@/components/AcademicYear/StatusBadge.vue';

// Modal
import ClassroomModal from './ClassroomModal.vue';

const props = defineProps({
    classrooms: Object,
    filters: Object,
    academicYears: Array,
    activeAcademicYear: Object,
    teachers: Array,
});

const search = ref(props.filters.search || '');
const selectedYear = ref(props.filters.academic_year_id || '');
const isModalOpen = ref(false);
const modalMode = ref('create');
const selectedClassroom = ref(null);

const openCreateModal = () => {
    modalMode.value = 'create';
    selectedClassroom.value = null;
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    modalMode.value = 'edit';
    selectedClassroom.value = { ...item };
    isModalOpen.value = true;
};

const deleteClassroom = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus kelas ini?')) {
        router.delete(route('classrooms.destroy', id));
    }
};

const submitSearch = () => {
    router.get(
        route('classrooms.index'),
        { search: search.value, academic_year_id: selectedYear.value },
        { preserveState: true, preserveScroll: true },
    );
};

const clearSearch = () => {
    search.value = '';
    selectedYear.value = '';
    submitSearch();
};

const handleModalSubmit = () => {
    router.reload();
};

import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

// Tambahkan state
const isStudentModalOpen = ref(false);
const selectedClassroomName = ref('');
const classroomStudents = ref([]);

// Method untuk fetch siswa
const showStudents = async (classroomId, className) => {
    try {
        const response = await axios.get(route('classrooms.students', { classroom: classroomId }));
        classroomStudents.value = response.data.students;
        selectedClassroomName.value = className;
        isStudentModalOpen.value = true;
    } catch (error) {
        console.error('Gagal fetch siswa:', error);
    }
};
</script>

<template>
    <AppLayout title="Data Kelas">
        <div v-if="!activeAcademicYear" class="mb-4 rounded-md bg-yellow-50 p-3 text-sm text-yellow-800">
            ⚠️ Tidak ada tahun ajaran yang aktif. Silakan atur tahun ajaran aktif di menu
            <Link :href="route('academic-years.index')" class="font-medium underline">Tahun Akademik</Link>.
        </div>
        <Card class="mx-auto w-full rounded-none border-0 border-b-0 shadow-none">
            <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle>Daftar Kelas</CardTitle>
                <Button @click="openCreateModal">
                    <Plus class="h-4 w-4" />
                    Tambah
                </Button>
            </CardHeader>
            <CardContent>
                <!-- Filters -->
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div class="flex flex-col gap-2 md:flex-row md:gap-4">
                        <div class="w-full md:w-64">
                            <label class="mb-1 block text-sm font-medium">Tahun Ajaran</label>
                            <select
                                v-model="selectedYear"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                                @change="submitSearch"
                            >
                                <option value="">Semua Tahun</option>
                                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                                    {{ year.name }} ({{ year.is_active ? 'Aktif' : 'Tidak Aktif' }})
                                </option>
                            </select>
                        </div>
                        <div class="w-full md:w-64">
                            <label class="mb-1 block text-sm font-medium">Cari Kelas</label>
                            <Input v-model="search" @keydown.enter.prevent="submitSearch" placeholder="Cari nama kelas..." class="w-full" />
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <Button variant="outline" @click="submitSearch"> Terapkan </Button>
                        <Button variant="ghost" @click="clearSearch"> Reset </Button>
                    </div>
                </div>

                <!-- Table -->
                <div class="mt-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama Kelas</TableHead>
                                <TableHead>Wali Kelas</TableHead>
                                <TableHead>Tahun Ajaran</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="item in classrooms.data" :key="item.id">
                                <TableCell class="font-medium">{{ item.name }}</TableCell>
                                <TableCell>{{ item.homeroom_teacher?.name || '-' }}</TableCell>
                                <TableCell>{{ item.academic_year?.name || '-' }}</TableCell>
                                <TableCell>
                                    <StatusBadge :is-active="Boolean(item.is_active)" />
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end space-x-2">
                                        <Button size="sm" variant="outline" @click="showStudents(item.id, item.name)"> Lihat Siswa </Button>
                                        <ActionButtons
                                            :is-active="Boolean(item.is_active)"
                                            @edit="openEditModal(item)"
                                            @delete="deleteClassroom(item.id)"
                                        />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="classrooms.data.length === 0">
                                <TableCell colspan="5" class="py-4 text-center"> Tidak ada data kelas. </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <Pagination
                        v-if="classrooms.links"
                        :meta="classrooms"
                        :links="classrooms.links"
                        :query-params="{ search: search, academic_year_id: selectedYear }"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Modal -->
        <ClassroomModal
            v-model:isOpen="isModalOpen"
            :mode="modalMode"
            :initialData="selectedClassroom"
            :academic-years="academicYears"
            :teachers="teachers"
            @submit="handleModalSubmit"
        />
    </AppLayout>

    <!-- Modal Daftar Siswa -->
    <Dialog v-model:open="isStudentModalOpen">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Daftar Siswa: {{ selectedClassroomName }}</DialogTitle>
                <DialogDescription> Berikut adalah daftar siswa yang terdaftar di kelas ini. </DialogDescription>
            </DialogHeader>
            <div class="mt-4 max-h-96 overflow-y-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>NIS</TableHead>
                            <TableHead>Nama</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="student in classroomStudents" :key="student.id">
                            <TableCell class="font-mono text-sm">{{ student.student_id }}</TableCell>
                            <TableCell>{{ student.name }}</TableCell>
                        </TableRow>
                        <TableRow v-if="classroomStudents.length === 0">
                            <TableCell colspan="2" class="py-4 text-center text-muted-foreground"> Tidak ada siswa di kelas ini. </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <DialogFooter class="mt-4">
                <Button @click="isStudentModalOpen = false"> Tutup </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
