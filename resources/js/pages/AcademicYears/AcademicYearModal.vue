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
    is_active: false,
});

watch(
    () => props.initialData,
    (newVal) => {
        if (props.mode === 'edit' && newVal) {
            form.reset();
            form.name = newVal.name || '';
            form.is_active = Boolean(newVal.is_active);
            form.clearErrors(); // jika ada error sebelumnya
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
        form.post(route('academic-years.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submit');
                emit('update:isOpen', false);
            },
        });
    } else {
        form.put(route('academic-years.update', props.initialData.id), {
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
                <DialogTitle>{{ mode === 'create' ? 'Tambah' : 'Edit' }} Tahun Akademik</DialogTitle>
                <DialogDescription>
                    {{
                        mode === 'create'
                            ? 'Isi form di bawah ini untuk menambahkan tahun akademik baru.'
                            : 'Perbarui informasi tahun akademik di bawah ini.'
                    }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="onSubmit" class="grid gap-4 py-4">
                <div class="grid grid-cols-4 items-center gap-4">
                    <Label for="name" class="text-right"> Nama </Label>
                    <div class="col-span-3">
                        <Input id="name" v-model="form.name" placeholder="Contoh: 2024/2025" />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>
                </div>
                <div class="grid grid-cols-4 items-center gap-4">
                    <Label for="is_active" class="text-right"> Status </Label>
                    <Select v-model="form.is_active">
                        <SelectTrigger id="is_active" class="col-span-3">
                            <SelectValue placeholder="Pilih Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="true"> Aktif </SelectItem>
                            <SelectItem :value="false"> Tidak Aktif </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.is_active" class="col-span-3 col-start-2 mt-2" />
                </div>
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
