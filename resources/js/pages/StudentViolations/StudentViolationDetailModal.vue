<!-- resources/js/Pages/StudentViolations/StudentViolationDetailModal.vue -->
<script setup>
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { formatTanggalIndo } from '@/lib/useTanggalIndo';
import { defineEmits, defineProps } from 'vue';

const props = defineProps({
    isOpen: { type: Boolean, required: true },
    violation: { type: Object, default: null },
});

const emit = defineEmits(['update:isOpen']);

const close = () => {
    emit('update:isOpen', false);
};
</script>

<template>
    <Dialog :open="isOpen" @update:open="close">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Detail Pelanggaran</DialogTitle>
                <DialogDescription> Informasi lengkap catatan pelanggaran siswa. </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label class="text-sm font-medium">Siswa</Label>
                        <div class="mt-1">{{ violation?.student?.name }}</div>
                    </div>
                    <div>
                        <Label class="text-sm font-medium">NIS</Label>
                        <div class="mt-1">{{ violation?.student?.student_id }}</div>
                    </div>
                </div>
                <div>
                    <Label class="text-sm font-medium">Kelas</Label>
                    <div class="mt-1">{{ violation?.classroom?.name }}</div>
                </div>
                <div>
                    <Label class="text-sm font-medium">Wali Kelas</Label>
                    <div class="mt-1">{{ violation?.homeroom_teacher?.name }}</div>
                </div>
                <div>
                    <Label class="text-sm font-medium">Jenis Pelanggaran</Label>
                    <div class="mt-1">{{ violation?.violation_type?.name }}</div>
                </div>
                <div>
                    <Label class="text-sm font-medium">Tanggal Kejadian</Label>
                    <div class="mt-1">{{ formatTanggalIndo(violation?.violation_date) }}</div>
                </div>
                <div>
                    <Label class="text-sm font-medium">Keterangan</Label>
                    <div class="mt-1 whitespace-pre-wrap">{{ violation?.notes }}</div>
                </div>
                <div>
                    <Label class="text-sm font-medium">Tindak Lanjut</Label>
                    <div class="mt-1 whitespace-pre-wrap">{{ violation?.follow_up || '-' }}</div>
                </div>
                <div>
                    <Label class="text-sm font-medium">Guru BK</Label>
                    <div class="mt-1">{{ violation?.counselor?.name || '-' }}</div>
                </div>
            </div>
            <DialogFooter>
                <Button @click="close"> Tutup </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
