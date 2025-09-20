<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

// Komponen baru
import ActionButtons from '@/components/AcademicYear/ActionButtons.vue';
import StatusBadge from '@/components/AcademicYear/StatusBadge.vue';
import AcademicYearModal from './AcademicYearModal.vue';

const props = defineProps({
    academicYears: Object, // kamu bisa tambahkan validator jika perlu
    filters: Object,
});

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const modalMode = ref('create'); // 'create' atau 'edit'
const selectedAcademicYear = ref(null); // gunakan null, bukan string 'null'

const openCreateModal = () => {
    modalMode.value = 'create';
    selectedAcademicYear.value = null;
    isModalOpen.value = true;
};

const openEditModal = (academicYear) => {
    modalMode.value = 'edit';
    // buat clone agar tidak langsung mutate data asli
    selectedAcademicYear.value = { ...academicYear };
    isModalOpen.value = true;
};

const deleteAcademicYear = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus tahun akademik ini?')) {
        router.delete(route('academic-years.destroy', id), {
            onSuccess: () => {
                // bisa panggil reload / fetch ulang supaya parent update
                // atau Inertia sudah handle redirect
            },
        });
    }
};

const submitSearch = () => {
    router.get(route('academic-years.index'), { search: search.value }, { preserveState: true, preserveScroll: true });
};

const clearSearch = () => {
    search.value = '';
    submitSearch();
};

const handleModalSubmit = () => {
    // Setelah modal submit sukses, fetch ulang tabel / reload
    router.reload(); // atau bisa router.get(...) untuk fetch ulang academicYears
};
</script>

<template>
    <AppLayout>
        <Card class="mx-auto w-full rounded-none border-0 border-b-0 shadow-none">
            <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle>Daftar Tahun Akademik</CardTitle>
                <Button @click="openCreateModal">
                    <Plus class="h-4 w-4" />
                    Tambah
                </Button>
            </CardHeader>
            <CardContent>
                <!-- Search Bar -->
                <div class="flex items-center py-4">
                    <Input v-model="search" @keydown.enter.prevent="submitSearch" placeholder="Cari tahun akademik..." class="max-w-sm" />
                    <Button variant="outline" class="ml-2" @click="submitSearch"> Cari </Button>
                    <Button variant="ghost" class="ml-2" @click="clearSearch" v-if="search"> Reset </Button>
                </div>

                <!-- Table -->
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="ay in academicYears.data" :key="ay.id">
                            <TableCell class="font-medium">{{ ay.name }}</TableCell>
                            <TableCell>
                                <StatusBadge :is-active="Boolean(ay.is_active)" />
                            </TableCell>
                            <TableCell class="text-right">
                                <ActionButtons :is-active="Boolean(ay.is_active)" @edit="openEditModal(ay)" @delete="deleteAcademicYear(ay.id)" />
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="academicYears.data.length === 0">
                            <TableCell colspan="3" class="py-4 text-center"> Tidak ada data tahun akademik. </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div v-if="academicYears.links" class="mt-4 flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Menampilkan {{ academicYears.from }} sampai {{ academicYears.to }} dari {{ academicYears.total }} data
                    </p>
                    <div class="flex space-x-2">
                        <Button
                            v-for="(link, index) in academicYears.links"
                            :key="index"
                            :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url"
                            @click="link.url ? router.get(link.url) : null"
                            v-html="link.label"
                            size="sm"
                        />
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Modal -->
        <AcademicYearModal v-model:isOpen="isModalOpen" :mode="modalMode" :initialData="selectedAcademicYear" @submit="handleModalSubmit" />
    </AppLayout>
</template>
