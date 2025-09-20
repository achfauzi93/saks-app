<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'violation_type_id',
        'classroom_id',
        'homeroom_teacher_id',
        'violation_date',
        'notes',
    ];

    protected $casts = [
        'violation_date' => 'date',
    ];

    // Relasi
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function violationType()
    {
        return $this->belongsTo(ViolationType::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }
}