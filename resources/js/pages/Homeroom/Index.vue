<!-- resources/js/Pages/Homeroom/Index.vue -->
<script setup>
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import {
    BookOpen, // Untuk Pelanggaran / Riwayat
    Calendar,
    ClipboardList, // Untuk tambah (akan dihapus)
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Props dari controller
const props = defineProps({
    classrooms: Array,
});
// State untuk tab aktif (opsional, bisa dikembangkan)
const activeTab = ref('overview');

// Hitung total siswa dan pelanggaran dari semua kelas
const totalStudents = computed(() => {
    return props.classrooms.reduce((sum, classroom) => {
        return sum + (classroom.students?.length || 0);
    }, 0);
});

const totalViolations = computed(() => {
    return props.classrooms.reduce((sum, classroom) => {
        return sum + (classroom.students?.reduce((sSum, student) => sSum + (student.violations_count || 0), 0) || 0);
    }, 0);
});

// Fungsi untuk membuka halaman daftar siswa
const goToStudentList = (classroomId) => {
    router.visit(route('homeroom.show', classroomId)); // Menuju ke halaman detail kelas
};
</script>

<template>
    <AppLayout title="Dashboard Wali Kelas">
        <div class="space-y-6 p-4 md:p-6">
            <!-- Header Dashboard -->
            <div>
                <h1 class="text-2xl font-bold md:text-3xl">Dashboard Wali Kelas</h1>
                <p class="text-muted-foreground">Kelola kelas binaan Anda dan pantau aktivitas siswa.</p>
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
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Pelanggaran</CardTitle>
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalViolations }}</div>
                        <p class="text-xs text-muted-foreground">Pelanggaran dari semua kelas</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Tabs untuk Navigasi -->
            <Tabs class="w-full" :defaultValue="activeTab">
                <TabsList class="grid w-full grid-cols-2 md:grid-cols-4">
                    <TabsTrigger value="overview">Gambaran Umum</TabsTrigger>
                    <TabsTrigger value="classes">Daftar Kelas</TabsTrigger>
                    <!-- <TabsTrigger value="reports">Laporan</TabsTrigger> -->
                    <!-- <TabsTrigger value="notifications">Notifikasi</TabsTrigger> -->
                </TabsList>

                <!-- Tab: Gambaran Umum -->
                <TabsContent value="overview" class="space-y-4">
                    <Alert>
                        <AlertTitle><Calendar class="mr-2 inline h-4 w-4" />Tahun Ajaran Aktif</AlertTitle>
                        <AlertDescription>
                            Anda saat ini mengelola kelas untuk tahun ajaran:
                            <span v-if="classrooms.length > 0 && classrooms[0].academic_year" class="font-semibold">
                                {{ classrooms[0].academic_year.name }}
                            </span>
                            <span v-else class="italic">Tidak ada tahun ajaran aktif yang ditemukan.</span>
                        </AlertDescription>
                    </Alert>

                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center"><Users class="mr-2 h-5 w-5" /> Kelas Binaan Anda</CardTitle>
                            <CardDescription>Daftar kelas yang Anda bimbing di tahun ajaran ini.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="classrooms.length === 0" class="py-4 text-center text-muted-foreground">
                                Anda tidak memiliki kelas binaan di tahun ajaran aktif ini.
                            </div>
                            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <Card v-for="classroom in classrooms" :key="classroom.id" class="transition-shadow hover:shadow-md">
                                    <CardHeader>
                                        <CardTitle class="text-lg">{{ classroom.name }}</CardTitle>
                                        <CardDescription> Tahun Ajaran: {{ classroom.academic_year?.name || 'N/A' }} </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div class="flex items-center justify-between text-sm">
                                            <span>Jumlah Siswa:</span>
                                            <Badge variant="secondary">{{ classroom.students?.length || 0 }}</Badge>
                                        </div>
                                        <div class="mt-1 flex items-center justify-between text-sm">
                                            <span>Pelanggaran:</span>
                                            <Badge variant="destructive">
                                                {{ classroom.students?.reduce((sum, s) => sum + (s.violations_count || 0), 0) || 0 }}
                                            </Badge>
                                        </div>
                                    </CardContent>
                                    <CardFooter class="flex justify-between">
                                        <Button variant="outline" size="sm" @click="goToStudentList(classroom.id)">
                                            <BookOpen class="mr-2 h-4 w-4" /> Lihat Siswa
                                        </Button>
                                    </CardFooter>
                                </Card>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Tab: Daftar Kelas (Tabel) -->
                <TabsContent value="classes">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center"><ClipboardList class="mr-2 h-5 w-5" /> Daftar Kelas dan Siswa</CardTitle>
                            <CardDescription>Detail siswa per kelas yang Anda bimbing.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="classrooms.length === 0" class="py-4 text-center text-muted-foreground">
                                Anda tidak memiliki kelas binaan di tahun ajaran aktif ini.
                            </div>
                            <div v-for="classroom in classrooms" :key="classroom.id" class="mb-6 last:mb-0">
                                <div class="mb-2 flex items-center justify-between">
                                    <h3 class="flex items-center text-lg font-semibold">
                                        {{ classroom.name }}
                                        <Badge variant="outline" class="ml-2">{{ classroom.students?.length || 0 }} Siswa</Badge>
                                    </h3>
                                    <div class="space-x-2">
                                        <Button variant="outline" size="sm" @click="goToStudentList(classroom.id)">
                                            <BookOpen class="mr-2 h-4 w-4" /> Daftar Siswa
                                        </Button>
                                    </div>
                                </div>
                                <Separator class="mb-2" />
                                <Table v-if="classroom.students && classroom.students.length > 0">
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead class="w-[100px]">NIS</TableHead>
                                            <TableHead>Nama</TableHead>
                                            <TableHead class="text-right">Pelanggaran</TableHead>
                                            <TableHead class="text-right">Aksi</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="student in classroom.students" :key="student.id">
                                            <TableCell class="font-medium">{{ student.student_id }}</TableCell>
                                            <TableCell>{{ student.name }}</TableCell>
                                            <TableCell class="text-right">
                                                <Badge :variant="student.violations_count > 0 ? 'destructive' : 'secondary'">
                                                    {{ student.violations_count }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button variant="link" size="sm" as-child>
                                                    <Link :href="route('homeroom.student-history', student.id)"> Lihat Riwayat </Link>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                                <div v-else class="py-2 text-center text-sm text-muted-foreground">Tidak ada siswa terdaftar di kelas ini.</div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Tambahkan style khusus jika diperlukan */
</style>
