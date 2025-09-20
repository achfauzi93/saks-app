<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClassAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'classroom_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relasi
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // Scope: cari assignment aktif (belum ada end_date)
    public function scopeActive($query)
    {
        return $query->whereNull('end_date');
    }

    // Scope: cari assignment pada tanggal tertentu
    public function scopeOnDate($query, $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->where('end_date', '>=', $date)
                    ->orWhereNull('end_date');
            });
    }
}