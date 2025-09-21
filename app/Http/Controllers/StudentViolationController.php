<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Models\ViolationType;
use App\Models\StudentViolation;
use App\Http\Requests\StoreStudentViolationRequest;

class StudentViolationController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = StudentViolation::with(['student', 'violationType', 'classroom', 'homeroomTeacher', 'counselor'])
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

        $counselors = User::role('guru-bk')->get();

        return Inertia::render('StudentViolations/Index', [
            'violations' => $violations,
            'filters' => $request->only('student_id', 'classroom_id', 'violation_type_id', 'date_from', 'date_to'),
            'students' => $students->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'student_id' => $s->student_id]),
            'classrooms' => $classrooms->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
            'violationTypes' => $violationTypes->map(fn($v) => ['id' => $v->id, 'name' => $v->name]),
            'activeAcademicYear' => $activeAcademicYear,
            'counselors' => $counselors->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return to_route('student-violations.index')->withError('Tidak ada tahun ajaran aktif. Silakan atur tahun ajaran aktif terlebih dahulu.');
        }

        // Hanya ambil siswa aktif di tahun ajaran aktif
        $students = Student::activeInAcademicYear($activeYear->id)->get();
        $counselors = User::role('guru-bk')->get();

        $violationTypes = ViolationType::where('is_active', true)->get();

        return Inertia::render('StudentViolations/Create', [
            'students' => $students->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'student_id' => $s->student_id,
            ]),
            'violationTypes' => $violationTypes->map(fn($v) => ['id' => $v->id, 'name' => $v->name]),
            'active_academic_year_id' => $activeYear->id,
            'counselors' => $counselors->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentViolationRequest $request)
    {
        $validated = $request->validated();

        $activeYear = active_academic_year();
        if (!$activeYear) {
            return to_route('student-violations.index')->withError('Tidak ada tahun ajaran aktif.');
        }

        $classroom = Classroom::where('academic_year_id', $activeYear->id)
            ->where('is_active', true)
            ->whereHas('students', function ($q) use ($validated) {
                $q->where('students.id', $validated['student_id']);
            })
            ->first();

        if (!$classroom) {
            return to_route('student-violations.index')->withError('Siswa tidak memiliki kelas aktif.');
        }

        $homeroomTeacherId = $classroom->homeroom_teacher_id;
        if (!$homeroomTeacherId) {
            return to_route('student-violations.index')->withError('Gagal menyimpan, Kelas belum memiliki walikelas, harap hubungi admin untuk penambahan walikelas.');
        }

        StudentViolation::create([
            'student_id' => $validated['student_id'],
            'violation_type_id' => $validated['violation_type_id'],
            'violation_date' => $validated['violation_date'],
            'classroom_id' => $classroom->id,
            'homeroom_teacher_id' => $homeroomTeacherId,
            'notes' => $validated['notes'],
            'follow_up' => $validated['follow_up'] ?? null,
            'counselor_id' => $validated['counselor_id'] ?? null,
        ]);

        return to_route('student-violations.index')->with('success', 'Catatan pelanggaran berhasil ditambahkan.');
    }

    public function destroy(StudentViolation $studentViolation)
    {
        $studentViolation->delete();
        return to_route('student-violations.index')->with('success', 'Catatan pelanggaran berhasil dihapus.');
    }

    /**
     * Ambil siswa berdasarkan kelas
     * @return \Illuminate\Http\JsonResponse
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentsByClassroom(Request $request): \Illuminate\Http\JsonResponse
    {
        $classroomId = $request->get('classroom_id');
        if (!$classroomId) {
            return response()->json([]);
        }

        $students = Student::whereHas('classrooms', function ($q) use ($classroomId) {
            $q->where('classroom_id', $classroomId);
        })->get();

        return response()->json(
            $students->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'student_id' => $s->student_id,
            ])
        );
    }
}