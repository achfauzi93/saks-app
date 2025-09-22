<!-- resources/js/Pages/StudentViolations/Index.vue -->
<script setup>
import Pagination from '@/components/Pagination.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';
// --- Impor Komponen Baru ---
import { Button } from '@/components/ui/button';
import StudentViolationDetailModal from './StudentViolationDetailModal.vue'; // Jika dibuat
import StudentViolationFilters from './StudentViolationFilters.vue';
import StudentViolationModal from './StudentViolationModal.vue';
import StudentViolationTable from './StudentViolationTable.vue';

const props = defineProps({
    violations: Object,
    filters: Object,
    students: Array,
    classrooms: Array,
    violationTypes: Array,
    activeAcademicYear: Object,
    counselors: Array,
});

// --- State untuk Modal ---
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const selectedViolation = ref(null);

// --- Fungsi untuk Modal ---
const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

const openEditModal = (violation) => {
    selectedViolation.value = violation;
    isEditModalOpen.value = true;
};

const openDetailModal = (violation) => {
    selectedViolation.value = violation;
    isDetailModalOpen.value = true;
};

const deleteViolation = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus catatan pelanggaran ini?')) {
        router.delete(route('student-violations.destroy', id));
    }
};

const handleModalSubmit = () => {
    router.reload();
};

// --- Fungsi untuk Filter ---
const applyFilters = (filterData) => {
    router.get(route('student-violations.index'), filterData, { preserveState: true, preserveScroll: true });
};

const resetFilters = () => {
    router.get(
        route('student-violations.index'),
        {}, // Kirim objek kosong untuk mereset semua parameter
        { preserveState: true, preserveScroll: true },
    );
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
                    Tambah
                </Button>
            </CardHeader>
            <CardContent>
                <!-- Gunakan komponen filter baru -->
                <StudentViolationFilters
                    :students="students"
                    :classrooms="classrooms"
                    :violation-types="violationTypes"
                    :counselors="counselors"
                    :filters="filters"
                    @apply-filters="applyFilters"
                    @reset-filters="resetFilters"
                />

                <!-- Table -->
                <div class="mt-6">
                    <!-- Gunakan komponen tabel baru -->
                    <StudentViolationTable
                        :violations="violations"
                        @edit-violation="openEditModal"
                        @delete-violation="deleteViolation"
                        @view-detail="openDetailModal"
                    />

                    <!-- Pagination -->
                    <Pagination v-if="violations.links" :meta="violations" :links="violations.links" :query-params="filters" />
                </div>
            </CardContent>
        </Card>

        <!-- Modal Create -->
        <StudentViolationModal
            v-model:isOpen="isCreateModalOpen"
            :students="students"
            :violation-types="violationTypes"
            :counselors="counselors"
            @submit="handleModalSubmit"
        />

        <!-- Modal Edit -->
        <StudentViolationModal
            v-model:isOpen="isEditModalOpen"
            mode="edit"
            :initial-data="selectedViolation"
            :violation-types="violationTypes"
            :counselors="counselors"
            @submit="handleModalSubmit"
        />

        <!-- Modal Detail -->
        <StudentViolationDetailModal
            v-model:is-open="isDetailModalOpen"
            :violation="selectedViolation"
            @update:is-open="isDetailModalOpen = $event"
        />
    </AppLayout>
</template>
