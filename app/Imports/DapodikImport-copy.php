<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Collection;

class DapodikImport implements WithMultipleSheets
{
    use SkipsFailures;

    public function sheets(): array
    {
        return [
            'students' => new class implements \Maatwebsite\Excel\Concerns\ToCollection, SkipsOnFailure {
                use SkipsFailures;

                public function collection(Collection $rows)
                {
                    foreach ($rows as $row) {
                        if ($row[0] == 'student_id') continue; // skip header

                        Student::updateOrCreate(
                            ['student_id' => $row[0]],
                            [
                                'name' => $row[1],
                                'email' => $row[2] ?? null,
                                'phone' => $row[3] ?? null,
                                'birth_date' => !empty($row[4]) ? $row[4] : null,
                                'address' => $row[5] ?? null,
                                'is_active' => true,
                            ]
                        );
                    }
                }
            },

            'classrooms' => new class implements \Maatwebsite\Excel\Concerns\ToCollection, SkipsOnFailure {
                use SkipsFailures;

                public function collection(Collection $rows)
                {
                    foreach ($rows as $row) {
                        if ($row[0] == 'name') continue; // skip header

                        $academicYear = AcademicYear::firstOrCreate(
                            ['name' => $row[1]],
                            ['is_active' => true] // default nonaktif
                        );

                        Classroom::updateOrCreate(
                            [
                                'name' => $row[0],
                                'academic_year_id' => $academicYear->id,
                            ],
                            [
                                'homeroom_teacher_id' => null, // biarkan null dulu
                                'is_active' => true,
                            ]
                        );
                    }
                }
            },

            'assignments' => new class implements \Maatwebsite\Excel\Concerns\ToCollection, SkipsOnFailure {
                use SkipsFailures;

                public function collection(Collection $rows)
                {
                    foreach ($rows as $row) {
                        if ($row[0] == 'student_id') continue; // skip header

                        $student = Student::where('student_id', $row[0])->first();
                        $academicYear = AcademicYear::where('name', $row[2])->first();
                        $classroom = Classroom::where('name', $row[1])
                            ->where('academic_year_id', $academicYear->id)
                            ->first();

                        if ($student && $classroom) {
                            // Cek apakah sudah ada assignment aktif
                            $activeAssignment = StudentClassAssignment::where('student_id', $student->id)
                                ->whereNull('end_date')
                                ->first();

                            if ($activeAssignment) {
                                $activeAssignment->update(['end_date' => now()->subDay()]);
                            }

                            StudentClassAssignment::updateOrCreate(
                                [
                                    'student_id' => $student->id,
                                    'classroom_id' => $classroom->id,
                                    'start_date' => $row[3],
                                ],
                                [
                                    'end_date' => null,
                                ]
                            );
                        }
                    }
                }
            },
        ];
    }
}