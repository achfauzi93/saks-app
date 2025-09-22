<!-- resources/js/Pages/ViolationTypes/Index.vue -->
<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

// Komponen pendukung (reusable dari AcademicYear, atau buat baru jika perlu)
import ActionButtons from '@/components/AcademicYear/ActionButtons.vue'; // Bisa dipakai ulang
import StatusBadge from '@/components/AcademicYear/StatusBadge.vue'; // Bisa dipakai ulang

// Modal
import ViolationTypeModal from './ViolationTypeModal.vue';

const props = defineProps({
    violationTypes: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const modalMode = ref('create');
const selectedViolationType = ref(null);

const openCreateModal = () => {
    modalMode.value = 'create';
    selectedViolationType.value = null;
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    modalMode.value = 'edit';
    selectedViolationType.value = { ...item };
    isModalOpen.value = true;
};

const deleteViolationType = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus jenis pelanggaran ini?')) {
        router.delete(route('violation-types.destroy', id));
    }
};

const submitSearch = () => {
    router.get(route('violation-types.index'), { search: search.value }, { preserveState: true, preserveScroll: true });
};

const clearSearch = () => {
    search.value = '';
    submitSearch();
};

const handleModalSubmit = () => {
    router.reload();
};
</script>

<template>
    <AppLayout title="Jenis Pelanggaran">
        <Card class="mx-auto w-full rounded-none border-0 border-b-0 shadow-none">
            <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle>Daftar Jenis Pelanggaran</CardTitle>
                <Button @click="openCreateModal">
                    <Plus class="h-4 w-4" />
                    Tambah
                </Button>
            </CardHeader>
            <CardContent>
                <!-- Search Bar -->
                <div class="flex items-center py-4">
                    <Input v-model="search" @keydown.enter.prevent="submitSearch" placeholder="Cari jenis pelanggaran..." class="max-w-sm" />
                    <Button variant="outline" class="ml-2" @click="submitSearch"> Cari </Button>
                    <Button variant="ghost" class="ml-2" @click="clearSearch" v-if="search"> Reset </Button>
                </div>

                <!-- Table -->
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama</TableHead>
                            <TableHead>Deskripsi</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="vt in violationTypes.data" :key="vt.id">
                            <TableCell class="font-medium">{{ vt.name }}</TableCell>
                            <TableCell>{{ vt.description || '-' }}</TableCell>
                            <TableCell>
                                <StatusBadge :is-active="Boolean(vt.is_active)" />
                            </TableCell>
                            <TableCell class="text-right">
                                <ActionButtons :is-active="Boolean(vt.is_active)" @edit="openEditModal(vt)" @delete="deleteViolationType(vt.id)" />
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="violationTypes.data.length === 0">
                            <TableCell colspan="4" class="py-4 text-center"> Tidak ada data jenis pelanggaran. </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div v-if="violationTypes.links" class="mt-4 flex items-center justify-between">
                    <p class="hidden text-sm text-muted-foreground md:block">
                        Tampil {{ violationTypes.from }} sampai {{ violationTypes.to }} dari {{ violationTypes.total }} data
                    </p>
                    <div class="flex space-x-2">
                        <Button
                            v-for="(link, index) in violationTypes.links"
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
        <ViolationTypeModal v-model:isOpen="isModalOpen" :mode="modalMode" :initialData="selectedViolationType" @submit="handleModalSubmit" />
    </AppLayout>
</template>
