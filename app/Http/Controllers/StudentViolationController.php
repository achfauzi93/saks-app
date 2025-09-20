<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use App\Models\StudentViolation;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentViolationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = StudentViolation::with(['student', 'violationType', 'classroom', 'homeroomTeacher'])
            ->latest();

        // Filter
        if ($studentId = $request->get('student_id')) {
            $query->where('student_id', $studentId);
        }

        if ($classroomId = $request->get('classroom_id')) {
            $query->where('classroom_id', $classroomId);
        }

        if ($violationTypeId = $request->get('violation_type_id')) {
            $query->where('violation_type_id', $violationTypeId);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->where('violation_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->where('violation_date', '<=', $dateTo);
        }

        $violations = $query->paginate(10);

        // Data untuk filter dropdown
        $students = Student::where('is_active', true)->get();
        $classrooms = Classroom::where('is_active', true)->get();
        $violationTypes = ViolationType::where('is_active', true)->get();
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        return Inertia::render('StudentViolations/Index', [
            'violations' => $violations,
            'filters' => $request->only('student_id', 'classroom_id', 'violation_type_id', 'date_from', 'date_to'),
            'students' => $students->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'student_id' => $s->student_id]),
            'classrooms' => $classrooms->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
            'violationTypes' => $violationTypes->map(fn($v) => ['id' => $v->id, 'name' => $v->name]),
            'activeAcademicYear' => $activeAcademicYear,
        ]);
    }

    public function create()
    {
        // Ambil tahun ajaran aktif
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return to_route('student-violations.index')->withError('Tidak ada tahun ajaran aktif. Silakan atur tahun ajaran aktif terlebih dahulu.');
        }

        // Hanya ambil siswa aktif di tahun ajaran aktif
        $students = Student::activeInAcademicYear($activeYear->id)->get();

        $violationTypes = ViolationType::where('is_active', true)->get();

        return Inertia::render('StudentViolations/Create', [
            'students' => $students->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'student_id' => $s->student_id,
            ]),
            'violationTypes' => $violationTypes->map(fn($v) => ['id' => $v->id, 'name' => $v->name]),
            'active_academic_year_id' => $activeYear->id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Cari assignment aktif pada tanggal kejadian
        $assignment = StudentClassAssignment::where('student_id', $validated['student_id'])
            ->where('start_date', '<=', $validated['violation_date'])
            ->where(function ($q) use ($validated) {
                $q->where('end_date', '>=', $validated['violation_date'])
                    ->orWhereNull('end_date');
            })
            ->first();

        if (!$assignment) {
            return to_route('student-violations.index')->withError('Siswa tidak terdaftar di kelas manapun pada tanggal tersebut.');
        }

        $classroom = $assignment->classroom;
        $homeroomTeacherId = $classroom->homeroom_teacher_id;

        // Simpan pelanggaran
        StudentViolation::create([
            'student_id' => $validated['student_id'],
            'violation_type_id' => $validated['violation_type_id'],
            'classroom_id' => $classroom->id,
            'homeroom_teacher_id' => $homeroomTeacherId,
            'violation_date' => $validated['violation_date'],
            'notes' => $validated['notes'],
        ]);

        return to_route('student-violations.index')->with('success', 'Catatan pelanggaran berhasil ditambahkan.');
    }

    public function destroy(StudentViolation $studentViolation)
    {
        $studentViolation->delete();
        return to_route('student-violations.index')->with('success', 'Catatan pelanggaran berhasil dihapus.');
    }


    public function getStudentsByClassroom(Request $request)
    {
        $classroomId = $request->get('classroom_id');
        $date = $request->get('date') ?? now()->toDateString();

        if (!$classroomId) {
            return response()->json([]);
        }

        // Cari siswa yang aktif di kelas ini pada tanggal tertentu
        $assignments = StudentClassAssignment::where('classroom_id', $classroomId)
            ->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->where('end_date', '>=', $date)
                    ->orWhereNull('end_date');
            })
            ->with('student')
            ->get();

        return response()->json(
            $assignments->map(fn($a) => [
                'id' => $a->student->id,
                'name' => $a->student->name,
                'student_id' => $a->student->student_id,
            ])
        );
    }
}