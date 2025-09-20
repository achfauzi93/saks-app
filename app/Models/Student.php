<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'name',
        'email',
        'phone',
        'birth_date',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'birth_date' => 'date',
    ];


    // relationships
    public function assignments()
    {
        return $this->hasMany(StudentClassAssignment::class);
    }


    public function scopeActiveInAcademicYear($query, $academicYearId)
    {
        return $query->whereHas('assignments', function ($q) use ($academicYearId) {
            $q->whereNull('end_date')
                ->whereHas('classroom', function ($c) use ($academicYearId) {
                    $c->where('academic_year_id', $academicYearId);
                });
        });
    }

    // Scope: siswa yang pernah aktif di tahun ajaran ini (untuk laporan historis)
    public function scopeEverInAcademicYear($query, $academicYearId)
    {
        return $query->whereHas('assignments', function ($q) use ($academicYearId) {
            $q->whereHas('classroom', function ($c) use ($academicYearId) {
                $c->where('academic_year_id', $academicYearId);
            });
        });
    }

    // assesors
    public function getIsActiveInCurrentAcademicYearAttribute()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return false;

        return $this->assignments()
            ->whereNull('end_date')
            ->whereHas('classroom', function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            })
            ->exists();
    }
}