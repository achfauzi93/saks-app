<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

    Route::resource('academic-years', \App\Http\Controllers\AcademicYearController::class)->except('show');
    Route::resource('violation-types', \App\Http\Controllers\ViolationTypeController::class)->except('show', 'create', 'edit');
    Route::resource('students', \App\Http\Controllers\StudentController::class);
    Route::post('students/import', [\App\Http\Controllers\StudentController::class, 'import'])->name('students.import');
    Route::post('students/import-dapodik', [\App\Http\Controllers\StudentController::class, 'importDapodik'])->name('students.import-dapodik');

    Route::resource('classrooms', \App\Http\Controllers\ClassroomController::class)->except('show', 'edit');

    // Student Class Assignments
    Route::get('student-class-assignments', [\App\Http\Controllers\StudentClassAssignmentController::class, 'index'])->name('student-class-assignments.index');
    Route::post('student-class-assignments', [\App\Http\Controllers\StudentClassAssignmentController::class, 'store'])->name('student-class-assignments.store');
    Route::post('student-class-assignments/transfer', [\App\Http\Controllers\StudentClassAssignmentController::class, 'transfer'])->name('student-class-assignments.transfer');
    Route::delete('student-class-assignments/{assignment}', [\App\Http\Controllers\StudentClassAssignmentController::class, 'destroy'])->name('student-class-assignments.destroy');


    Route::resource('student-violations', \App\Http\Controllers\StudentViolationController::class)->except(['show', 'edit', 'update']);
    Route::get('student-violations/students-by-classroom', [\App\Http\Controllers\StudentViolationController::class, 'getStudentsByClassroom'])
        ->name('student-violations.students-by-classroom');
});



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';