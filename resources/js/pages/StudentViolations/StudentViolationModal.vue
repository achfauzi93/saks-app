<!-- resources/js/Pages/StudentViolations/StudentViolationModal.vue -->
<script setup>
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { defineEmits, defineProps, ref, watch } from 'vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    violationTypes: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['update:isOpen', 'submit']);

const classrooms = ref([]);
const selectedClassroom = ref('');
const studentsInClassroom = ref([]);
const isLoadingStudents = ref(false);

const form = useForm({
    student_id: '',
    violation_type_id: '',
    violation_date: new Date().toISOString().split('T')[0],
    notes: '',
});

// ✅ Perbaikan: fetchClassrooms tetap pakai axios
const fetchClassrooms = async () => {
    try {
        const response = await axios.get(
            route('classrooms.index', {
                academic_year_id: 'active',
                only_classrooms: true,
            }),
        );
        classrooms.value = response.data.classrooms || [];
    } catch (error) {
        console.error('Gagal fetch kelas:', error);
        classrooms.value = [];
    }
};

// ✅ Perbaikan: fetchStudentsByClassroom jadi async function — TANPA nested function
const fetchStudentsByClassroom = async () => {
    if (!selectedClassroom.value) {
        studentsInClassroom.value = [];
        form.student_id = '';
        return;
    }

    isLoadingStudents.value = true;

    try {
        const response = await axios.get(route('student-violations.students-by-classroom'), {
            params: {
                classroom_id: selectedClassroom.value,
                date: form.violation_date,
            },
        });

        studentsInClassroom.value = response.data;
        form.student_id = '';
    } catch (error) {
        console.error('Gagal fetch siswa:', error);
        studentsInClassroom.value = [];
    } finally {
        isLoadingStudents.value = false;
    }
};

// Watchers
watch(selectedClassroom, fetchStudentsByClassroom);

watch(
    () => form.violation_date,
    () => {
        if (selectedClassroom.value) {
            fetchStudentsByClassroom();
        }
    },
);

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            fetchClassrooms();
            selectedClassroom.value = '';
            studentsInClassroom.value = [];
            form.reset();
            form.violation_date = new Date().toISOString().split('T')[0];
        } else {
            form.clearErrors();
        }
    },
    { immediate: true },
);

const onSubmit = () => {
    form.post(route('student-violations.store'), {
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
                <DialogTitle>Tambah Catatan Pelanggaran</DialogTitle>
                <DialogDescription> Pilih kelas, lalu pilih siswa yang melakukan pelanggaran. </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="onSubmit" class="grid gap-4 py-4">
                <!-- Kelas -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="classroom_id">Kelas *</Label>
                    <Select v-model="selectedClassroom">
                        <SelectTrigger id="classroom_id" class="w-full">
                            <SelectValue placeholder="Pilih Kelas" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                {{ classroom.name }} ({{ classroom.academic_year?.name }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Siswa -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="student_id">Siswa *</Label>
                    <Select v-model="form.student_id" :disabled="!selectedClassroom || isLoadingStudents">
                        <SelectTrigger id="student_id" class="w-full">
                            <SelectValue placeholder="Pilih Siswa" />
                        </SelectTrigger>
                        <SelectContent>
                            <!-- Loading -->
                            <SelectItem v-if="isLoadingStudents" disabled value="__loading__">
                                <div class="flex items-center">
                                    <svg class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Memuat siswa...
                                </div>
                            </SelectItem>

                            <!-- Daftar siswa -->
                            <SelectItem v-for="student in studentsInClassroom" :key="student.id" :value="student.id">
                                {{ student.name }} ({{ student.student_id }})
                            </SelectItem>

                            <!-- Empty state -->
                            <SelectItem v-if="!isLoadingStudents && studentsInClassroom.length === 0 && selectedClassroom" disabled value="__empty__">
                                Tidak ada siswa di kelas ini
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.student_id" />
                </div>

                <!-- Jenis Pelanggaran -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="violation_type_id">Jenis Pelanggaran *</Label>
                    <Select v-model="form.violation_type_id">
                        <SelectTrigger id="violation_type_id" class="w-full">
                            <SelectValue placeholder="Pilih Jenis Pelanggaran" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="type in violationTypes" :key="type.id" :value="type.id">
                                {{ type.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.violation_type_id" />
                </div>

                <!-- Tanggal Kejadian -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="violation_date">Tanggal Kejadian *</Label>
                    <Input id="violation_date" type="date" v-model="form.violation_date" />
                    <InputError :message="form.errors.violation_date" />
                </div>

                <!-- Keterangan -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="notes">Keterangan</Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        placeholder="Opsional..."
                    ></textarea>
                    <InputError :message="form.errors.notes" />
                </div>

                <!-- Submit -->
                <DialogFooter>
                    <Button type="submit" :disabled="form.processing || !form.student_id">
                        <span v-if="form.processing"> Menyimpan... </span>
                        <span v-else> Simpan </span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
