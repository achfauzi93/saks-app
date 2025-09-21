<!-- resources/js/Pages/StudentViolations/StudentViolationTable.vue -->
<script setup>
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { formatTanggalIndo } from '@/lib/useTanggalIndo';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { defineEmits, defineProps } from 'vue';

const props = defineProps({
    violations: { type: Object, required: true }, // Data dari paginasi Inertia
});

const emit = defineEmits(['editViolation', 'deleteViolation', 'viewDetail']);

const openEditModal = (violation) => {
    emit('editViolation', violation);
};

const openDetailModal = (violation) => {
    emit('viewDetail', violation);
};

const deleteViolation = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus catatan pelanggaran ini?')) {
        emit('deleteViolation', id);
    }
};
</script>

<template>
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
                <TableCell>{{ formatTanggalIndo(item.violation_date) }}</TableCell>
                <TableCell>
                    <div class="line-clamp-2 max-w-xs">
                        {{ item.notes || '-' }}
                    </div>
                    <Button v-if="item.notes" variant="link" size="sm" class="h-auto p-0" @click="openDetailModal(item)"> Lihat lengkap </Button>
                </TableCell>
                <TableCell class="text-right">
                    <div class="flex justify-end gap-1">
                        <Button size="sm" variant="outline" @click="openEditModal(item)">
                            <Pencil class="h-3 w-3" />
                        </Button>
                        <Button size="sm" variant="destructive" @click="deleteViolation(item.id)">
                            <Trash2 class="h-3 w-3" />
                        </Button>
                    </div>
                </TableCell>
            </TableRow>
            <TableRow v-if="violations.data.length === 0">
                <TableCell colspan="7" class="py-4 text-center"> Tidak ada data pelanggaran. </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
