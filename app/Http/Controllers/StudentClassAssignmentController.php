<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentClassAssignmentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = StudentClassAssignment::with(['student', 'classroom'])
            ->orderBy('name', 'asc');

        // Ambil tahun ajaran aktif
        $activeYear = AcademicYear::where('is_active', true)->first();

        // Filter per kelas
        if ($classroomId = $request->get('classroom_id')) {
            $query->where('classroom_id', $classroomId);
        }

        // Filter per siswa
        if ($studentId = $request->get('student_id')) {
            $query->where('student_id', $studentId);
        }

        // Filter status: aktif atau semua
        if ($request->get('status') === 'active' && $activeYear) {
            $query->whereHas('classroom', function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            })->whereNull('end_date');
        }


        $assignments = $query->paginate(10);

        // Data untuk filter dropdown — hanya kelas aktif di tahun ajaran aktif
        $classrooms = Classroom::when($activeYear, function ($q) use ($activeYear) {
            return $q->where('academic_year_id', $activeYear->id);
        })->where('is_active', true)->get();

        // Siswa aktif di tahun ajaran aktif
        $students = $activeYear ? Student::activeInAcademicYear($activeYear->id)->get() : collect();

        return Inertia::render('StudentClassAssignments/Index', [
            'assignments' => $assignments,
            'filters' => $request->only('classroom_id', 'student_id', 'status'),
            'classrooms' => $classrooms->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ]),
            'students' => $students->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'student_id' => $s->student_id,
            ]),
            'activeAcademicYear' => $activeYear,
        ]);
    }

    // Assign siswa ke kelas
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'start_date' => 'required|date',
        ]);

        $assignments = [];

        foreach ($validated['student_ids'] as $studentId) {
            // Cek apakah siswa ini sudah punya assignment aktif
            $activeAssignment = StudentClassAssignment::where('student_id', $studentId)
                ->whereNull('end_date')
                ->first();

            if ($activeAssignment) {
                // Jika sudah aktif di kelas lain → auto pindah (set end_date)
                $activeAssignment->update(['end_date' => now()->subDay()]);
            }

            // Buat assignment baru
            $assignments[] = StudentClassAssignment::create([
                'student_id' => $studentId,
                'classroom_id' => $validated['classroom_id'],
                'start_date' => $validated['start_date'],
                'end_date' => null, // aktif
            ]);
        }

        return to_route('student-class-assignments.index')->with('success', 'Siswa berhasil ditugaskan ke kelas.');
    }

    // Pindah kelas (set end_date lama, buat baru)
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'new_classroom_id' => 'required|exists:classrooms,id',
            'transfer_date' => 'required|date',
        ]);

        // Cari assignment aktif
        $currentAssignment = StudentClassAssignment::where('student_id', $validated['student_id'])
            ->whereNull('end_date')
            ->first();

        if ($currentAssignment) {
            // Set end_date assignment lama
            $currentAssignment->update(['end_date' => $validated['transfer_date']->subDay()]);
        }

        // Buat assignment baru
        StudentClassAssignment::create([
            'student_id' => $validated['student_id'],
            'classroom_id' => $validated['new_classroom_id'],
            'start_date' => $validated['transfer_date'],
            'end_date' => null,
        ]);

        return to_route('student-class-assignments.index')->with('success', 'Siswa berhasil dipindahkan ke kelas baru.');
    }

    // Hapus assignment (opsional — hati-hati)
    public function destroy(StudentClassAssignment $assignment)
    {
        $assignment->delete();
        return to_route('student-class-assignments.index')->with('success', 'Assignment berhasil dihapus.');
    }

    public function getActiveStudents(Request $request)
    {
        $yearId = $request->get('academic_year_id');
        if (!$yearId) {
            $yearId = AcademicYear::where('is_active', true)->first()?->id;
        }

        $students = Student::activeInAcademicYear($yearId)->get();

        return response()->json($students->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'student_id' => $s->student_id,
        ]));
    }
}