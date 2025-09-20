<!-- resources/js/Pages/Students/StudentModal.vue -->
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
    mode: {
        type: String,
        required: true,
        validator: (value) => ['create', 'edit'].includes(value),
    },
    initialData: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:isOpen', 'submit']);

const form = useForm({
    student_id: '',
    name: '',
    email: '',
    phone: '',
    birth_date: '',
    address: '',
    is_active: true,
});

watch(
    () => props.initialData,
    (newVal) => {
        if (props.mode === 'edit' && newVal) {
            form.reset();
            form.student_id = newVal.student_id || '';
            form.name = newVal.name || '';
            form.email = newVal.email || '';
            form.phone = newVal.phone || '';
            form.birth_date = newVal.birth_date || '';
            form.address = newVal.address || '';
            form.is_active = Boolean(newVal.is_active);
            form.clearErrors();
        }
    },
    { immediate: true },
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
    if (props.mode === 'create') {
        form.post(route('students.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submit');
                emit('update:isOpen', false);
            },
        });
    } else {
        form.put(route('students.update', props.initialData.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submit');
                emit('update:isOpen', false);
            },
        });
    }
};
</script>

<template>
    <Dialog :open="isOpen" @update:open="$emit('update:isOpen', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ mode === 'create' ? 'Tambah' : 'Edit' }} Siswa</DialogTitle>
                <DialogDescription>
                    {{ mode === 'create' ? 'Isi form di bawah ini untuk menambahkan siswa baru.' : 'Perbarui informasi siswa di bawah ini.' }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="onSubmit" class="grid gap-4 py-4">
                <!-- NIS -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="student_id">NIS/NISN *</Label>
                    <Input id="student_id" type="text" placeholder="Contoh: 1234567890" v-model="form.student_id" />
                    <InputError :message="form.errors.student_id" />
                </div>

                <!-- Nama -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="name">Nama Lengkap *</Label>
                    <Input id="name" type="text" placeholder="Contoh: Ahmad Fauzi" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" placeholder="Contoh: ahmad@email.com" v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Telepon -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="phone">Telepon</Label>
                    <Input id="phone" type="text" placeholder="Contoh: 081234567890" v-model="form.phone" />
                    <InputError :message="form.errors.phone" />
                </div>

                <!-- Tanggal Lahir -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="birth_date">Tanggal Lahir</Label>
                    <Input id="birth_date" type="date" v-model="form.birth_date" />
                    <InputError :message="form.errors.birth_date" />
                </div>

                <!-- Alamat -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="address">Alamat</Label>
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        placeholder="Alamat lengkap..."
                    ></textarea>
                    <InputError :message="form.errors.address" />
                </div>

                <!-- Status -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="is_active">Status *</Label>
                    <Select v-model="form.is_active">
                        <SelectTrigger id="is_active" class="w-full">
                            <SelectValue placeholder="Pilih Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="true"> Aktif </SelectItem>
                            <SelectItem :value="false"> Tidak Aktif </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.is_active" />
                </div>

                <!-- Submit -->
                <DialogFooter>
                    <Button type="submit" :disabled="form.processing">
                        <span v-if="form.processing">
                            {{ mode === 'create' ? 'Menyimpan...' : 'Memperbarui...' }}
                        </span>
                        <span v-else>
                            {{ mode === 'create' ? 'Simpan' : 'Perbarui' }}
                        </span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
