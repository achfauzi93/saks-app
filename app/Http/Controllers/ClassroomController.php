<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;


class ClassroomController extends Controller
{
    // Helper: dapatkan ID tahun ajaran aktif, atau null jika tidak ada
    private function getActiveAcademicYearId()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        return $activeYear ? $activeYear->id : null;
    }

    public function index(Request $request)
    {
        $query = Classroom::with(['homeroomTeacher', 'academicYear']);

        // Default filter: tahun ajaran aktif
        $defaultYearId = $this->getActiveAcademicYearId();
        $selectedYearId = $request->get('academic_year_id', $defaultYearId);

        if ($selectedYearId === 'active') {
            $activeYear = AcademicYear::where('is_active', true)->first();
            $selectedYearId = $activeYear ? $activeYear->id : null;
        }

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }

        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $classrooms = $query->orderBy('name', 'asc')->paginate(10);

        // data untuk dropdown
        if ($request->get('only_classrooms')) {
            $allClassrooms = Classroom::with('academicYear')
                ->where('academic_year_id', $selectedYearId ?? $defaultYearId)
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'classrooms' => $allClassrooms->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'academic_year' => [
                        'name' => $c->academicYear->name ?? null,
                    ],
                ]),
            ]);
        }

        // Response normal untuk Inertia (dengan pagination)
        $academicYears = AcademicYear::all();
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $teachers = User::role('guru')->orderBy('name', 'asc')->get();

        return Inertia::render('Classrooms/Index', [
            'classrooms' => $classrooms, // ← INI TETAP PAGINATE
            'filters' => [
                'search' => $request->get('search', ''),
                'academic_year_id' => $selectedYearId,
            ],
            'academicYears' => $academicYears,
            'activeAcademicYear' => $activeAcademicYear,
            'teachers' => $teachers->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:classrooms,name',
            'homeroom_teacher_id' => 'required|exists:users,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'is_active' => 'required|boolean',
        ]);

        Classroom::create($validated);

        return to_route('classrooms.index')->with('success', 'Kelas berhasil ditambahkan.');
    }


    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classrooms', 'name')->ignore($classroom->id),
            ],
            'homeroom_teacher_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($classroom) {
                    if ($value) {
                        $alreadyAssigned = \App\Models\Classroom::where('homeroom_teacher_id', $value)
                            ->where('academic_year_id', $classroom->academic_year_id)
                            ->where('id', '!=', $classroom->id) // kecuali dirinya sendiri
                            ->exists();

                        if ($alreadyAssigned) {
                            $fail('Guru ini sudah menjadi wali kelas lain.');
                        }
                    }
                },
            ],
            'academic_year_id' => 'required|exists:academic_years,id',
            'is_active' => 'required|boolean',
        ]);

        $classroom->update($validated);

        return to_route('classrooms.index')->with('success', 'Kelas berhasil diperbarui.');
    }


    public function destroy(Classroom $classroom)
    {
        // Cek apakah masih ada siswa yang aktif di kelas ini? (nanti setelah buat assignment)
        $classroom->delete();

        return to_route('classrooms.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function getStudents(Classroom $classroom)
    {
        $students = Student::whereHas('classrooms', function ($q) use ($classroom) {
            $q->where('classroom_id', $classroom->id);
        })->get();

        return response()->json([
            'classroom' => $classroom->only('id', 'name'),
            'students' => $students->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'student_id' => $s->student_id,
            ]),
        ]);
    }
}