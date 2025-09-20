<!-- resources/js/Pages/Classrooms/ClassroomModal.vue -->
<script setup>
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { computed, defineEmits, defineProps, watch } from 'vue';

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
    academicYears: {
        type: Array,
        required: true,
    },
    teachers: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['update:isOpen', 'submit']);

// Dapatkan ID tahun ajaran aktif dari props.academicYears
const activeAcademicYearId = computed(() => {
    const active = props.academicYears.find((year) => year.is_active);
    return active ? active.id : null;
});

const form = useForm({
    name: '',
    homeroom_teacher_id: '',
    academic_year_id: activeAcademicYearId.value || '',
    is_active: true,
});

watch(
    () => props.initialData,
    (newVal) => {
        if (props.mode === 'edit' && newVal) {
            form.reset();
            form.name = newVal.name || '';
            form.homeroom_teacher_id = newVal.homeroom_teacher_id || '';
            form.academic_year_id = newVal.academic_year_id || '';
            form.is_active = Boolean(newVal.is_active);
            form.clearErrors();
        } else if (props.mode === 'create') {
            // Reset ke default saat create
            form.academic_year_id = activeAcademicYearId.value || '';
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
        form.post(route('classrooms.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('submit');
                emit('update:isOpen', false);
            },
        });
    } else {
        form.put(route('classrooms.update', props.initialData.id), {
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
                <DialogTitle>{{ mode === 'create' ? 'Tambah' : 'Edit' }} Kelas</DialogTitle>
                <DialogDescription>
                    {{ mode === 'create' ? 'Isi form di bawah ini untuk menambahkan kelas baru.' : 'Perbarui informasi kelas di bawah ini.' }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="onSubmit" class="grid gap-4 py-4">
                <!-- Nama Kelas -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="name">Nama Kelas *</Label>
                    <Input id="name" type="text" placeholder="Contoh: X IPA 1" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Tahun Ajaran -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="academic_year_id">Tahun Ajaran *</Label>
                    <Select v-model="form.academic_year_id">
                        <SelectTrigger id="academic_year_id" class="w-full">
                            <SelectValue placeholder="Pilih Tahun Ajaran" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="year in academicYears" :key="year.id" :value="year.id">
                                {{ year.name }} ({{ year.is_active ? 'Aktif' : 'Tidak Aktif' }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.academic_year_id" />
                </div>

                <!-- Wali Kelas -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="homeroom_teacher_id">Wali Kelas *</Label>
                    <Select v-model="form.homeroom_teacher_id">
                        <SelectTrigger id="homeroom_teacher_id" class="w-full">
                            <SelectValue placeholder="Pilih Wali Kelas" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                {{ teacher.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.homeroom_teacher_id" />
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
