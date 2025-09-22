<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    /**
     * Menampilkan halaman utama absensi.
     * Menampilkan daftar kelas yang diwalasi user (jika walas) atau semua kelas (jika admin).
     */
    public function index(): Response
    {
        $user = auth()->user();
        $activeYear = active_academic_year();
        $classrooms = collect(); // Inisialisasi sebagai collection kosong

        if ($user->hasRole('guru')) {
            // Jika user adalah walas, ambil hanya kelas binaannya
            $classrooms = $user->homeroomClasses()
                ->where('academic_year_id', $activeYear?->id)
                ->where('is_active', true)
                ->with(['academicYear', 'students']) // Eager load untuk menghindari N+1)
                ->get();
        } else {
            // Jika bukan walas (misal: admin, guru piket jika ada permission)
            // Cek permission untuk melihat semua kelas absensi
            // if ($user->can('view-all-attendances')) { // Ganti dengan permission yang sesuai
            $classrooms = Classroom::where('academic_year_id', $activeYear?->id)
                ->where('is_active', true)
                ->with(['academicYear', 'students']) // Eager load untuk menghindari N+1
                ->orderBy('name', 'asc')
                ->get();
            // }
        }

        return Inertia::render('Attendances/Index', [
            'classrooms' => $classrooms,
            'activeAcademicYear' => $activeYear,
        ]);
    }

    /**
     * Menampilkan form untuk mengelola absensi kelas tertentu di tanggal tertentu.
     */
    public function show(Classroom $classroom, Request $request)
    {
        $user = auth()->user();
        $date = $request->get('date', now()->toDateString()); // Default ke hari ini


        // Validasi: Apakah user memiliki akses ke kelas ini?
        $activeYear = active_academic_year();
        if ($user->hasRole('guru')) {
            // Jika walas, pastikan dia walas kelas ini
            if ($classroom->homeroom_teacher_id !== $user->id || $classroom->academic_year_id !== $activeYear?->id) {
                abort(403, 'Anda tidak memiliki akses ke kelas ini.');
            }
        }
        // Jika bukan walas, bisa tambahkan policy untuk role lain (admin, dll.)

        // Ambil daftar siswa di kelas ini
        $students = $classroom->students()->where('is_active', true)->get();

        // Ambil data absensi yang sudah ada untuk tanggal ini
        $existingAttendances = Attendance::where('classroom_id', $classroom->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id'); // Gunakan student_id sebagai key untuk pencarian cepat




        return Inertia::render('Attendances/Show', [
            'classroom' => $classroom,
            'date' => $date,
            'students' => $students,
            'attendances' => $existingAttendances, // Kirim sebagai associative array
            'activeAcademicYear' => $activeYear,
        ]);
    }

    public function store(Request $request, Classroom $classroom)
    {
        $user = auth()->user();
        $activeYear = active_academic_year();

        // cek apakah kelas sudah ada walikelasnya
        if (!$classroom->homeroom_teacher_id) {
            return back()->withError('Kelas ini belum memiliki walikelas.');
        }
        // --- 1. Validasi Akses ---
        if ($user->hasRole('guru')) {
            if ($classroom->homeroom_teacher_id !== $user->id || $classroom->academic_year_id !== $activeYear?->id) {
                return back()->withError('Anda tidak memiliki akses untuk mengelola absensi kelas ini.');
            }
        }
        // Tambahkan policy untuk role lain jika diperlukan
        // $this->authorize('manage-attendance', $classroom);

        // --- 2. Validasi Data Input ---
        $validated = $request->validate([
            'date' => 'required|date|before_or_equal:today', // Tidak boleh di masa depan
            // Validasi array attendance_data
            'attendance_data' => 'required|array',
            'attendance_data.*.student_id' => [
                'required',
                'exists:students,id',
                // Pastikan student_id benar-benar ada di kelas ini
                Rule::exists('student_classroom', 'student_id')->where(function ($query) use ($classroom) {
                    $query->where('classroom_id', $classroom->id);
                }),
            ],
            'attendance_data.*.status' => 'required|in:present,sick,leave,absent',
            'attendance_data.*.is_late' => 'nullable|boolean',
            'attendance_data.*.late_time' => 'nullable|date_format:H:i', // Format jam:menit
            'attendance_data.*.notes' => 'nullable|string|max:255',
        ], [
            // Custom message untuk validasi exists student di kelas
            'attendance_data.*.student_id.exists' => 'Salah satu siswa tidak terdaftar di kelas ini.',
        ]);

        $attendanceDate = $validated['date'];
        $attendancesToProcess = $validated['attendance_data'];

        try {
            DB::beginTransaction();

            foreach ($attendancesToProcess as $data) {
                $studentId = $data['student_id'];

                // --- 3. Siapkan Data untuk Disimpan ---
                $attendanceData = [
                    'student_id' => $studentId,
                    'classroom_id' => $classroom->id,
                    'academic_year_id' => $activeYear->id,
                    'homeroom_teacher_id' => $classroom->homeroom_teacher_id,
                    'date' => $attendanceDate,
                    'status' => $data['status'],
                    'is_late' => filter_var($data['is_late'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'late_time' => $data['late_time'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'recorded_by' => $user->id, // Audit trail
                ];

                // --- 4. Cek apakah sudah ada record untuk kombinasi ini ---
                $existingAttendance = Attendance::where('student_id', $studentId)
                    ->where('classroom_id', $classroom->id)
                    ->whereDate('date', $attendanceDate)
                    ->first();

                if ($existingAttendance) {
                    // --- 5a. Jika ada, update ---
                    $existingAttendance->update($attendanceData);
                } else {
                    // --- 5b. Jika tidak ada, buat baru ---
                    Attendance::create($attendanceData);
                }
            }

            DB::commit();

            return back()->with('success', 'Data absensi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menyimpan absensi: " . $e->getMessage(), [
                'user_id' => $user->id,
                'classroom_id' => $classroom->id,
                'date' => $attendanceDate,
                'data' => $attendancesToProcess,
            ]);

            return back()->withError('Terjadi kesalahan saat menyimpan data absensi. Silakan coba lagi.' . $e->getMessage());
        }
    }
}