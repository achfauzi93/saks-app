<!-- resources/js/Pages/Homeroom/Show.vue -->
<script setup>
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

const props = defineProps({
    classroom: Object,
    students: Array,
});
</script>

<template>
    <AppLayout :title="`Kelas ${classroom.name}`">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Kelas {{ classroom.name }}</h1>
                    <p class="text-muted-foreground">Tahun Ajaran: {{ classroom.academic_year?.name }}</p>
                </div>
                <Link :href="route('student-violations.create')" class="flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Catat Pelanggaran
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Daftar Siswa Binaan</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-3 text-left">NIS</th>
                                    <th class="py-3 text-left">Nama</th>
                                    <th class="py-3 text-left">Jumlah Pelanggaran</th>
                                    <th class="py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="student in students" :key="student.id" class="border-b">
                                    <td class="py-3 font-mono text-sm">{{ student.student_id }}</td>
                                    <td class="py-3">{{ student.name }}</td>
                                    <td class="py-3">{{ student.violations_count }}</td>
                                    <td class="py-3 text-right">
                                        <Link
                                            :href="route('student-violations.index', { student_id: student.id })"
                                            class="text-sm text-blue-600 hover:underline"
                                        >
                                            Lihat Pelanggaran
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="students.length === 0">
                                    <td colspan="4" class="py-4 text-center text-muted-foreground">Tidak ada siswa di kelas ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
