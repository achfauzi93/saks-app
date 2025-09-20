<!-- resources/js/Pages/Students/Index.vue -->
<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Plus, Upload } from 'lucide-vue-next';
import { ref } from 'vue';

// Komponen pendukung
import ActionButtons from '@/components/AcademicYear/ActionButtons.vue';
import StatusBadge from '@/components/AcademicYear/StatusBadge.vue';

// Modal
import StudentModal from './StudentModal.vue';

const props = defineProps({
    students: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const isImportModalOpen = ref(false);
const modalMode = ref('create');
const selectedStudent = ref(null);
const importFile = ref(null);

const openCreateModal = () => {
    modalMode.value = 'create';
    selectedStudent.value = null;
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    modalMode.value = 'edit';
    selectedStudent.value = { ...item };
    isModalOpen.value = true;
};

const deleteStudent = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus siswa ini?')) {
        router.delete(route('students.destroy', id));
    }
};

const submitSearch = () => {
    router.get(route('students.index'), { search: search.value }, { preserveState: true, preserveScroll: true });
};

const clearSearch = () => {
    search.value = '';
    submitSearch();
};

const handleModalSubmit = () => {
    router.reload();
};

// Import
const triggerImport = () => {
    isImportModalOpen.value = true;
};

const handleImport = () => {
    if (!importFile.value) {
        alert('Pilih file terlebih dahulu.');
        return;
    }

    const formData = new FormData();
    formData.append('file', importFile.value);

    router.post(route('students.import'), formData, {
        onSuccess: () => {
            isImportModalOpen.value = false;
            importFile.value = null;
        },
    });
};

const paginationQueryParams = computed(() => ({
    search: search.value,
    // tambahkan filter lain di sini
}));

const isDapodikModalOpen = ref(false);
const dapodikFile = ref(null);

const triggerDapodikImport = () => {
    isDapodikModalOpen.value = true;
};

const handleDapodikImport = () => {
    if (!dapodikFile.value) {
        alert('Pilih file terlebih dahulu.');
        return;
    }

    const formData = new FormData();
    formData.append('file', dapodikFile.value);

    router.post(route('students.import-dapodik'), formData, {
        onSuccess: () => {
            isDapodikModalOpen.value = false;
            dapodikFile.value = null;
        },
    });
};
</script>

<template>
    <AppLayout title="Data Siswa">
        <Card class="mx-auto w-full rounded-none border-0 border-b-0 shadow-none">
            <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle>Daftar Siswa</CardTitle>
                <div class="flex space-x-2">
                    <Button @click="triggerDapodikImport" variant="secondary" class="ml-2">
                        <Upload class="h-4 w-4" />
                        Import Dapodik
                    </Button>
                    <Button @click="triggerImport">
                        <Upload class="h-4 w-4" />
                        Import
                    </Button>
                    <Button @click="openCreateModal">
                        <Plus class="h-4 w-4" />
                        Tambah
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <!-- Search Bar -->
                <div class="flex items-center py-4">
                    <Input v-model="search" @keydown.enter.prevent="submitSearch" placeholder="Cari siswa (nama/NIS)..." class="max-w-sm" />
                    <Button variant="outline" class="ml-2" @click="submitSearch"> Cari </Button>
                    <Button variant="ghost" class="ml-2" @click="clearSearch" v-if="search"> Reset </Button>
                </div>

                <!-- Table -->
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>NIS</TableHead>
                            <TableHead>Nama</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="student in students.data" :key="student.id">
                            <TableCell class="font-mono text-sm">{{ student.student_id }}</TableCell>
                            <TableCell class="font-medium">{{ student.name }}</TableCell>
                            <TableCell>{{ student.email || '-' }}</TableCell>
                            <TableCell>
                                <StatusBadge :is-active="Boolean(student.is_active)" />
                            </TableCell>
                            <TableCell class="text-right">
                                <ActionButtons
                                    :is-active="Boolean(student.is_active)"
                                    @edit="openEditModal(student)"
                                    @delete="deleteStudent(student.id)"
                                />
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="students.data.length === 0">
                            <TableCell colspan="5" class="py-4 text-center"> Tidak ada data siswa. </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <Pagination v-if="students.links" :meta="students" :links="students.links" :query-params="paginationQueryParams" />
            </CardContent>
        </Card>

        <!-- Modal Create/Edit -->
        <StudentModal v-model:isOpen="isModalOpen" :mode="modalMode" :initialData="selectedStudent" @submit="handleModalSubmit" />

        <!-- Modal Import -->
        <Dialog v-model:open="isImportModalOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Import Data Siswa</DialogTitle>
                    <DialogDescription>
                        Unggah file Excel (.xlsx) dengan format kolom: <br />
                        <code>student_id, name, email, phone, birth_date, address, is_active</code>
                    </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid w-full max-w-sm items-center gap-1.5">
                        <Label for="import_file">File Excel *</Label>
                        <Input id="import_file" type="file" @change="(e) => (importFile = e.target.files[0])" accept=".xlsx,.xls,.csv" />
                        <p class="mt-1 text-xs text-muted-foreground">Format: .xlsx, .xls, .csv (maks 2MB)</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isImportModalOpen = false"> Batal </Button>
                    <Button @click="handleImport" :disabled="!importFile"> Import </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Modal Import Dapodik -->
        <Dialog v-model:open="isDapodikModalOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Import Data Dapodik</DialogTitle>
                    <DialogDescription> Unggah file Excel dengan 3 sheet: students, classrooms, assignments. </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid w-full max-w-sm items-center gap-1.5">
                        <Label for="dapodik_file">File Excel *</Label>
                        <Input id="dapodik_file" type="file" @change="(e) => (dapodikFile = e.target.files[0])" accept=".xlsx,.xls" />
                        <p class="mt-1 text-xs text-muted-foreground">Format: .xlsx, .xls (maks 2MB)</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isDapodikModalOpen = false"> Batal </Button>
                    <Button @click="handleDapodikImport" :disabled="!dapodikFile"> Import </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<!-- Tambahkan Dialog Component di sini jika belum global -->
<script>
import Pagination from '@/components/Pagination.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { computed } from 'vue';

export default {
    components: {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogHeader,
        DialogTitle,
        Label,
        Input,
        Button,
    },
};
</script>
