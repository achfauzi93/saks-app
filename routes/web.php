<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\HomeroomController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ViolationTypeController;
use App\Http\Controllers\StudentViolationController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::group(['middleware' => ['auth']], function () {
    Route::get('roles/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    Route::resource('roles', RoleController::class);
    Route::resource('users', \App\Http\Controllers\UserController::class)->except('show');

    Route::resource('academic-years', AcademicYearController::class)->except('show');
    Route::resource('violation-types', ViolationTypeController::class)->except('show', 'create', 'edit');
    Route::resource('students', StudentController::class);
    Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
    Route::post('students/import-dapodik', [StudentController::class, 'importDapodik'])->name('students.import-dapodik');

    Route::resource('classrooms', ClassroomController::class)->except('show', 'edit');
    Route::get('classrooms/{classroom}/students', [ClassroomController::class, 'getStudents'])
        ->name('classrooms.students');


    Route::resource('student-violations', StudentViolationController::class)->except(['show', 'edit', 'update']);
    Route::get('student-violations/students-by-classroom', [StudentViolationController::class, 'getStudentsByClassroom'])
        ->name('student-violations.students-by-classroom');

    // Homeroom — hanya untuk guru yang jadi walas
    Route::middleware(['auth'])->group(function () {
        Route::get('homeroom', [HomeroomController::class, 'index'])->name('homeroom.index');
        Route::get('homeroom/{classroom}', [HomeroomController::class, 'show'])->name('homeroom.show');
    });
});



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';