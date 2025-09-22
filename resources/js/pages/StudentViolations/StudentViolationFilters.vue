<!-- resources/js/Pages/StudentViolations/StudentViolationFilters.vue -->
<script setup>
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { computed, defineEmits, defineProps, ref } from 'vue';

const props = defineProps({
    students: { type: Array, required: true },
    classrooms: { type: Array, required: true },
    violationTypes: { type: Array, required: true },
    counselors: { type: Array, required: true },
    filters: { type: Object, required: true }, // Untuk inisialisasi
});

const emit = defineEmits(['applyFilters', 'resetFilters']);

// Local state untuk filter
const selectedStudent = ref(props.filters.student_id || '');
const selectedClassroom = ref(props.filters.classroom_id || '');
const selectedViolationType = ref(props.filters.violation_type_id || '');
const selectedCounselor = ref(props.filters.counselor_id || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const studentSearch = ref('');
const classroomSearch = ref('');

// Filtered data untuk dropdown
const filteredStudents = computed(() => {
    if (!studentSearch.value) return props.students;
    return props.students.filter(
        (s) => s.name.toLowerCase().includes(studentSearch.value.toLowerCase()) || s.student_id.includes(studentSearch.value),
    );
});

const filteredClassrooms = computed(() => {
    if (!classroomSearch.value) return props.classrooms;
    return props.classrooms.filter((c) => c.name.toLowerCase().includes(classroomSearch.value.toLowerCase()));
});

const applyFilters = () => {
    emit('applyFilters', {
        student_id: selectedStudent.value,
        classroom_id: selectedClassroom.value,
        violation_type_id: selectedViolationType.value,
        counselor_id: selectedCounselor.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    });
};

const resetFilters = () => {
    selectedStudent.value = '';
    selectedClassroom.value = '';
    selectedViolationType.value = '';
    selectedCounselor.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    studentSearch.value = '';
    classroomSearch.value = '';
    // Emit dengan nilai kosong untuk mereset
    emit('resetFilters');
};

defineExpose({
    // Untuk memungkinkan parent mengakses nilai filter saat ini jika diperlukan
    getFilterValues: () => ({
        student_id: selectedStudent.value,
        classroom_id: selectedClassroom.value,
        violation_type_id: selectedViolationType.value,
        counselor_id: selectedCounselor.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }),
});
</script>

<template>
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-6">
            <div>
                <Label class="mb-1 block text-sm font-medium">Cari Siswa</Label>
                <Input v-model="studentSearch" placeholder="Cari NIS/Nama..." class="mb-1" @input="selectedStudent = ''" />
                <select
                    v-model="selectedStudent"
                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                >
                    <option value="">Pilih Siswa</option>
                    <option v-for="student in filteredStudents" :key="student.id" :value="student.id">
                        {{ student.name }} ({{ student.student_id }})
                    </option>
                </select>
            </div>
            <div>
                <Label class="mb-1 block text-sm font-medium">Cari Kelas</Label>
                <Input v-model="classroomSearch" placeholder="Cari Kelas..." class="mb-1" @input="selectedClassroom = ''" />
                <select
                    v-model="selectedClassroom"
                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                >
                    <option value="">Pilih Kelas</option>
                    <option v-for="classroom in filteredClassrooms" :key="classroom.id" :value="classroom.id">
                        {{ classroom.name }}
                    </option>
                </select>
            </div>
            <div>
                <Label class="mb-1 block text-sm font-medium">Jenis Pelanggaran</Label>
                <select
                    v-model="selectedViolationType"
                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                >
                    <option value="">Semua Jenis</option>
                    <option v-for="type in violationTypes" :key="type.id" :value="type.id">
                        {{ type.name }}
                    </option>
                </select>
            </div>
            <div>
                <Label class="mb-1 block text-sm font-medium">Guru BK</Label>
                <select
                    v-model="selectedCounselor"
                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                >
                    <option value="">Semua Guru BK</option>
                    <option v-for="counselor in counselors" :key="counselor.id" :value="counselor.id">
                        {{ counselor.name }}
                    </option>
                </select>
            </div>
            <div>
                <Label class="mb-1 block text-sm font-medium">Dari Tanggal</Label>
                <Input v-model="dateFrom" type="date" />
            </div>
            <div>
                <Label class="mb-1 block text-sm font-medium">Sampai Tanggal</Label>
                <Input v-model="dateTo" type="date" />
            </div>
        </div>
        <div class="flex space-x-2">
            <Button variant="outline" @click="applyFilters"> Terapkan </Button>
            <Button variant="destructive" @click="resetFilters"> Reset </Button>
        </div>
    </div>
</template>
