<!-- resources/js/Pages/Homeroom/StudentHistory.vue -->
<script setup>
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';
import { BookOpen, User } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    student: Object,
    violations: Array,
});

// Hitung total pelanggaran
const totalViolations = computed(() => props.violations.length);

// Hitung total poin (jika ada field poin di violation_type)
// Misal: violation_type.point
const totalPoints = computed(() => {
    return props.violations.reduce((sum, violation) => {
        return sum + (violation.violation_type?.point || 0);
    }, 0);
});
</script>

<template>
    <AppLayout :title="`Riwayat Pelanggaran - ${student.name}`">
        <div class="space-y-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <Button variant="outline" as-child>
                    <!-- Tambahkan pengecekan jika classrooms ada dan tidak kosong -->
                    <Link v-if="student.classrooms && student.classrooms.length > 0" :href="route('homeroom.index')"> Kembali </Link>
                    <!-- Opsional: Tambahkan link alternatif atau pesan jika tidak ada kelas -->
                    <span v-else class="text-muted-foreground">Tidak ada data kelas untuk navigasi kembali.</span>
                </Button>
                <h1 class="mt-4 text-2xl font-bold md:text-3xl">Riwayat Pelanggaran Siswa</h1>
                <p class="text-muted-foreground">Biodata dan riwayat pelanggaran {{ student.name }} selama sekolah.</p>
            </div>

            <!-- Biodata Siswa -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="h-5 w-5" />
                        Biodata Siswa
                    </CardTitle>
                    <CardDescription>Informasi pribadi dan kontak siswa.</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-4 md:grid-cols-2">
                    <div>
                        <h3 class="font-semibold">Identitas</h3>
                        <dl class="mt-2 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                            <dt class="text-muted-foreground">Nama</dt>
                            <dd>{{ student.name }}</dd>

                            <dt class="text-muted-foreground">NIS/NISN</dt>
                            <dd>{{ student.student_id }}</dd>

                            <dt class="text-muted-foreground">Tanggal Lahir</dt>
                            <dd>{{ student.birth_date ? format(new Date(student.birth_date), 'dd MMMM yyyy', { locale: id }) : '-' }}</dd>

                            <dt class="text-muted-foreground">Jenis Kelamin</dt>
                            <dd>{{ student.gender || '-' }}</dd>
                            <!-- Jika ada field gender -->
                        </dl>
                    </div>
                    <div>
                        <h3 class="font-semibold">Kontak & Alamat</h3>
                        <dl class="mt-2 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                            <dt class="text-muted-foreground">Alamat</dt>
                            <dd>{{ student.address || '-' }}</dd>

                            <dt class="text-muted-foreground">Telepon</dt>
                            <dd>{{ student.phone || '-' }}</dd>

                            <dt class="text-muted-foreground">Email</dt>
                            <dd>{{ student.email || '-' }}</dd>
                        </dl>
                    </div>
                </CardContent>
            </Card>

            <!-- Ringkasan Statistik -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Pelanggaran</CardTitle>
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalViolations }}</div>
                        <p class="text-xs text-muted-foreground">Catatan pelanggaran</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Poin</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalPoints }}</div>
                        <p class="text-xs text-muted-foreground">Poin pelanggaran</p>
                    </CardContent>
                </Card>
                <!-- Bisa tambah card lain, misal: Kelas Terakhir, Walas Terakhir, dll -->
            </div>

            <!-- Riwayat Pelanggaran -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <BookOpen class="h-5 w-5" />
                        Riwayat Pelanggaran
                    </CardTitle>
                    <CardDescription>Daftar semua pelanggaran yang dilakukan siswa selama sekolah.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="violations.length === 0" class="py-4 text-center text-muted-foreground">
                        Siswa ini tidak memiliki riwayat pelanggaran.
                    </div>
                    <Table v-else>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>Jenis Pelanggaran</TableHead>
                                <TableHead>Kelas & Tahun Ajaran</TableHead>
                                <TableHead>Wali Kelas</TableHead>
                                <TableHead>Poin</TableHead>
                                <TableHead>Keterangan</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="violation in violations" :key="violation.id">
                                <TableCell class="font-medium">
                                    {{ violation.violation_date ? format(new Date(violation.violation_date), 'dd/MM/yyyy', { locale: id }) : '-' }}
                                </TableCell>
                                <TableCell>
                                    {{ violation.violation_type?.name || '-' }}
                                    <div v-if="violation.violation_type?.point" class="text-xs text-muted-foreground">
                                        ({{ violation.violation_type.point }} poin)
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {{ violation.classroom?.name || '-' }}
                                    <div class="text-xs text-muted-foreground">
                                        {{ violation.classroom?.academic_year?.name || '-' }}
                                    </div>
                                </TableCell>
                                <TableCell>{{ violation.homeroom_teacher?.name || '-' }}</TableCell>
                                <TableCell>
                                    <Badge variant="destructive">{{ violation.violation_type?.point || 0 }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="max-w-xs truncate" :title="violation.notes || '-'">{{ violation.notes || '-' }}</div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Tambahkan style khusus jika diperlukan */
</style>
