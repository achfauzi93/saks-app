<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'classroom_id',
        'academic_year_id',
        'homeroom_teacher_id',
        'date',
        'status',
        'is_late',
        'late_time',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_late' => 'boolean',
        'late_time' => 'datetime:H:i', // Format jam:menit
    ];

    // --- Relasi ---
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}