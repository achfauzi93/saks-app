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
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil kelas yang diwalasi user ini
        $classrooms = $user->homeroomClasses()
            ->with(['academicYear', 'students' => function ($q) {
                $q->withCount('violations');
            }])
            ->get();

        return Inertia::render('Homeroom/Index', [
            'classrooms' => $classrooms,
        ]);
    }

    public function show(Classroom $classroom)
    {
        // Pastikan user ini adalah walas kelas ini
        if ($classroom->homeroom_teacher_id !== Auth::user()->id) {
            abort(403);
        }

        $students = Student::whereHas('classrooms', function ($q) use ($classroom) {
            $q->where('classroom_id', $classroom->id);
        })->withCount('violations')->get();

        return Inertia::render('Homeroom/Show', [
            'classroom' => $classroom->load('academicYear'),
            'students' => $students,
        ]);
    }
}