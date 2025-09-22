<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentViolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class HomeroomController extends Controller
{
    /**
     * Menampilkan dashboard wali kelas.
     * Hanya menampilkan kelas dan siswa binaan.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Ambil kelas yang diwalasi user ini beserta siswa dan jumlah pelanggaran mereka
        $classrooms = $user->homeroomClasses()
            ->with(['academicYear', 'students' => function ($q) {
                // Eager load jumlah pelanggaran untuk setiap siswa
                $q->withCount('violations');
            }])
            ->get();

        return Inertia::render('Homeroom/Index', [
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Menampilkan detail satu kelas binaan beserta siswanya.
     * (Digunakan saat mengklik "Lihat Siswa" dari dashboard)
     */
    public function show(Classroom $classroom): Response
    {
        $user = Auth::user();

        // Validasi: Apakah user ini adalah walas kelas ini?
        if ($classroom->homeroom_teacher_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Load data siswa dengan jumlah pelanggaran
        $students = Student::whereHas('classrooms', function ($q) use ($classroom) {
            $q->where('classroom_id', $classroom->id);
        })->withCount('violations')->get();

        return Inertia::render('Homeroom/Show', [
            'classroom' => $classroom->load('academicYear'),
            'students' => $students,
        ]);
    }

    /**
     * Menampilkan biodata siswa dan riwayat pelanggarannya selama sekolah.
     * (Ini adalah fitur utama yang diminta)
     */
    public function showStudentHistory(Student $student): Response
    {
        $user = Auth::user();

        // --- Validasi Hak Akses ---
        // Cek apakah siswa ini berada di salah satu kelas binaan user (di tahun ajaran aktif)
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            abort(403, 'Tidak ada tahun ajaran aktif.');
        }

        $isStudentInHomeroomClass = Classroom::where('academic_year_id', $activeYear->id)
            ->where('homeroom_teacher_id', $user->id)
            ->whereHas('students', function ($q) use ($student) {
                $q->where('students.id', $student->id);
            })
            ->exists();

        if (!$isStudentInHomeroomClass) {
            abort(403, 'Anda tidak memiliki akses untuk melihat riwayat siswa ini.');
        }
        // --- Akhir Validasi Hak Akses ---

        // --- Ambil Data yang Diperlukan ---
        // 1. Biodata Siswa (sudah ada di $student)
        // 2. Riwayat Pelanggaran (semua, tidak hanya di tahun aktif)
        //    Kita bisa eager load relasi yang dibutuhkan untuk menampilkan detail lengkap.
        $violations = StudentViolation::where('student_id', $student->id)
            ->with(['violationType', 'classroom.academicYear', 'homeroomTeacher', 'counselor'])
            ->orderBy('violation_date', 'desc')
            ->get();

        // 3. (Opsional) Informasi kelas-kelas yang pernah diikuti siswa
        //    (Ini bisa kompleks jika ingin menampilkan histori kelas)
        //    Untuk sekarang, kita fokus pada pelanggaran.

        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $studentWithClassrooms = Student::where('id', $student->id)
            ->whereHas('classrooms', function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id ?? 0); // Hindari error jika tidak ada tahun aktif
            })
            ->with('classrooms') // Eager load relasi classrooms
            ->firstOrFail(); // Pastikan siswa ditemukan
        // --- Akhir Perbaikan ---

        return Inertia::render('Homeroom/StudentHistory', [
            // Kirim objek $student yang sudah dimuat relasi classrooms-nya
            'student' => $studentWithClassrooms,
            'violations' => $violations,
        ]);
    }
}