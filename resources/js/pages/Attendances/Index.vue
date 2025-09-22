<!-- resources/js/Pages/Attendances/Index.vue -->
<script setup>
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    classrooms: Array,
    activeAcademicYear: Object,
});

// Hitung total siswa
const totalStudents = computed(() => {
    return props.classrooms.reduce((sum, classroom) => {
        return sum + (classroom.students?.length || 0);
    }, 0);
});
</script>

<template>
    <AppLayout title="Absensi Siswa">
        <div class="space-y-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold md:text-3xl">Absensi Siswa</h1>
                <p class="text-muted-foreground">Kelola absensi harian siswa di kelas binaan Anda.</p>
            </div>

            <div v-if="!activeAcademicYear" class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                ⚠️ Tidak ada tahun ajaran aktif. Silakan atur tahun ajaran aktif terlebih dahulu.
            </div>

            <!-- Ringkasan Statistik -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Kelas</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ classrooms.length }}</div>
                        <p class="text-xs text-muted-foreground">Kelas yang Anda bimbing</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Siswa</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalStudents }}</div>
                        <p class="text-xs text-muted-foreground">Siswa di bawah bimbingan Anda</p>
                    </CardContent>
                </Card>
                <!-- Bisa tambah statistik lain seperti total absen hari ini, dll -->
            </div>

            <!-- Daftar Kelas -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <Users class="mr-2 h-5 w-5" />
                        Kelas Binaan Anda
                    </CardTitle>
                    <p class="text-sm text-muted-foreground">Pilih kelas untuk mengelola absensi harian siswa.</p>
                </CardHeader>
                <CardContent>
                    <div v-if="classrooms.length === 0" class="py-4 text-center text-muted-foreground">
                        Anda tidak memiliki kelas binaan di tahun ajaran aktif ini.
                    </div>
                    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="classroom in classrooms" :key="classroom.id" class="transition-shadow hover:shadow-md">
                            <CardHeader>
                                <CardTitle class="text-lg">{{ classroom.name }}</CardTitle>
                                <p class="text-sm text-muted-foreground">Tahun Ajaran: {{ classroom.academic_year?.name || 'N/A' }}</p>
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-center justify-between text-sm">
                                    <span>Jumlah Siswa:</span>
                                    <span class="font-medium">{{ classroom.students?.length || 0 }}</span>
                                </div>
                            </CardContent>
                            <CardContent>
                                <Link
                                    :href="route('attendances.show', { classroom: classroom.id, date: new Date().toISOString().split('T')[0] })"
                                    class="inline-flex h-10 w-full items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                >
                                    Kelola Absensi
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
