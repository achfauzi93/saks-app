<!-- resources/js/Pages/StudentClassAssignments/TransferModal.vue -->
<script setup>
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { defineEmits, defineProps, watch } from 'vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    student: {
        type: Object,
        default: null,
    },
    classrooms: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['update:isOpen', 'submit']);

const form = useForm({
    student_id: props.student?.id || '',
    new_classroom_id: '',
    transfer_date: new Date().toISOString().split('T')[0],
});

watch(
    () => props.student,
    (newVal) => {
        if (newVal) {
            form.student_id = newVal.id;
        }
    },
);

watch(
    () => props.isOpen,
    (open) => {
        if (!open) {
            form.reset();
            form.clearErrors();
        }
    },
);

const onSubmit = () => {
    form.post(route('student-class-assignments.transfer'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('submit');
            emit('update:isOpen', false);
        },
    });
};
</script>

<template>
    <Dialog :open="isOpen" @update:open="$emit('update:isOpen', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Pindahkan Siswa ke Kelas Lain</DialogTitle>
                <DialogDescription>
                    Pindahkan {{ student?.name }} ke kelas baru. Tanggal akhir di kelas lama akan di-set otomatis.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="onSubmit" class="grid gap-4 py-4">
                <!-- Siswa (readonly) -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="student_name">Siswa</Label>
                    <Input id="student_name" :value="student?.name" disabled />
                </div>

                <!-- Kelas Baru -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="new_classroom_id">Kelas Baru *</Label>
                    <Select v-model="form.new_classroom_id">
                        <SelectTrigger id="new_classroom_id" class="w-full">
                            <SelectValue placeholder="Pilih Kelas Baru" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                {{ classroom.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.new_classroom_id" />
                </div>

                <!-- Tanggal Pindah -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="transfer_date">Tanggal Pindah *</Label>
                    <Input id="transfer_date" type="date" v-model="form.transfer_date" />
                    <InputError :message="form.errors.transfer_date" />
                </div>

                <!-- Submit -->
                <DialogFooter>
                    <Button type="submit" :disabled="form.processing">
                        <span v-if="form.processing"> Memindahkan... </span>
                        <span v-else> Pindahkan </span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
