<!-- resources/js/Pages/StudentClassAssignments/AssignModal.vue -->
<script setup>
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { computed, defineEmits, defineProps, ref, watch } from 'vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    classrooms: {
        type: Array,
        required: true,
    },
    students: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['update:isOpen', 'submit']);

const searchStudent = ref('');
const selectedStudents = ref([]);

const filteredStudents = computed(() => {
    if (!searchStudent.value) return props.students;
    return props.students.filter(
        (s) => s.name.toLowerCase().includes(searchStudent.value.toLowerCase()) || s.student_id.includes(searchStudent.value),
    );
});

const form = useForm({
    classroom_id: '',
    start_date: new Date().toISOString().split('T')[0], // default hari ini
    student_ids: [],
});

watch(
    () => props.isOpen,
    (open) => {
        if (!open) {
            form.reset();
            form.clearErrors();
            selectedStudents.value = [];
        }
    },
);

const toggleStudent = (studentId) => {
    const index = selectedStudents.value.indexOf(studentId);
    if (index === -1) {
        selectedStudents.value.push(studentId);
    } else {
        selectedStudents.value.splice(index, 1);
    }
    form.student_ids = [...selectedStudents.value];
};

const onSubmit = () => {
    form.post(route('student-class-assignments.store'), {
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
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Assign Siswa ke Kelas</DialogTitle>
                <DialogDescription> Pilih satu atau lebih siswa untuk ditugaskan ke kelas tertentu. </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="onSubmit" class="grid gap-4 py-4">
                <!-- Kelas -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="classroom_id">Kelas *</Label>
                    <Select v-model="form.classroom_id">
                        <SelectTrigger id="classroom_id" class="w-full">
                            <SelectValue placeholder="Pilih Kelas" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                {{ classroom.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.classroom_id" />
                </div>

                <!-- Tanggal Mulai -->
                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="start_date">Tanggal Mulai *</Label>
                    <Input id="start_date" type="date" v-model="form.start_date" />
                    <InputError :message="form.errors.start_date" />
                </div>

                <!-- Daftar Siswa -->
                <div class="space-y-2">
                    <Label>Daftar Siswa *</Label>
                    <Input v-model="searchStudent" placeholder="Cari siswa (nama/NIS)..." class="mb-2" />
                    <div class="max-h-60 overflow-y-auto rounded-md border">
                        <div
                            v-for="student in filteredStudents"
                            :key="student.id"
                            class="flex items-center space-x-2 border-b px-3 py-2 last:border-b-0 hover:bg-muted"
                        >
                            <input
                                type="checkbox"
                                :id="`student-${student.id}`"
                                :value="student.id"
                                :checked="selectedStudents.includes(student.id)"
                                @change="toggleStudent(student.id)"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label :for="`student-${student.id}`" class="flex-1 cursor-pointer py-1 text-sm">
                                {{ student.name }} <span class="text-muted-foreground">({{ student.student_id }})</span>
                            </label>
                        </div>
                    </div>
                    <InputError :message="form.errors.student_ids" />
                </div>

                <!-- Submit -->
                <DialogFooter>
                    <Button type="submit" :disabled="form.processing || form.student_ids.length === 0">
                        <span v-if="form.processing"> Menyimpan... </span>
                        <span v-else> Simpan ({{ form.student_ids.length }} siswa) </span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
