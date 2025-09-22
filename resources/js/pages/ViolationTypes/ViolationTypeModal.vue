<!-- resources/js/Pages/ViolationTypes/ViolationTypeModal.vue -->
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
    name: '',
    description: '',
    is_active: true,
});

watch(
    () => props.initialData,
    (newVal) => {
        if (props.mode === 'edit' && newVal) {
            form.reset();
            form.name = newVal.name || '';
            form.description = newVal.description || '';
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
        form.post(route('violation-types.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submit');
                emit('update:isOpen', false);
            },
        });
    } else {
        form.put(route('violation-types.update', props.initialData.id), {
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
                <DialogTitle>{{ mode === 'create' ? 'Tambah' : 'Edit' }} Jenis Pelanggaran</DialogTitle>
                <DialogDescription>
                    {{
                        mode === 'create'
                            ? 'Isi form di bawah ini untuk menambahkan jenis pelanggaran baru.'
                            : 'Perbarui informasi jenis pelanggaran di bawah ini.'
                    }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="onSubmit" class="grid gap-4 py-4">
                <!-- Nama -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="name">Nama *</Label>
                    <Input id="name" type="text" placeholder="Contoh: Terlambat Masuk" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Deskripsi -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="description">Deskripsi</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        placeholder="Opsional, jelaskan detail pelanggaran..."
                    ></textarea>
                    <InputError :message="form.errors.description" />
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
