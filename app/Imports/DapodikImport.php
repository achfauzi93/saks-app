<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DapodikImport implements WithMultipleSheets
{
    use SkipsFailures;

    protected $academicYearId;
    protected $importedStudentIds = []; // 👈 Kumpulkan di sini

    public function __construct()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            throw new \Exception('Tidak ada tahun ajaran aktif. Silakan atur tahun ajaran aktif terlebih dahulu.');
        }
        $this->academicYearId = $activeYear->id;
    }

    public function getAcademicYearId()
    {
        return $this->academicYearId;
    }

    public function addImportedStudentId($studentId)
    {
        $this->importedStudentIds[] = $studentId;
    }

    public function getImportedStudentIds()
    {
        return $this->importedStudentIds;
    }


    public function sheets(): array
    {
        return [
            'Daftar Peserta Didik' => new class($this) implements \Maatwebsite\Excel\Concerns\ToCollection, SkipsOnFailure {
                use SkipsFailures;

                private $parent;

                public function __construct($parent)
                {
                    $this->parent = $parent;
                }

                public function collection(Collection $rows)
                {
                    $processedClassrooms = [];

                    foreach ($rows as $index => $row) {
                        // Skip header
                        if ($index === 0 || $row[0] == 'No') continue;

                        // 1. Simpan Siswa
                        $student = Student::updateOrCreate(
                            ['student_id' => $row[4]], // NIPD
                            [
                                'name' => $row[1], // Nama
                                'email' => null,
                                'phone' => $row[5] ?? null, // HP
                                'birth_date' => !empty($row[6]) ? $row[6] : null, // Tanggal Lahir
                                'address' => $row[8] ?? null, // Alamat
                                'is_active' => true,
                            ]
                        );

                        $this->parent->addImportedStudentId($row[4]); // Kumpulkan student_id

                        // 2. Simpan Kelas
                        $className = $row[42] ?? null; // Rombel Saat Ini
                        if ($className && !isset($processedClassrooms[$className])) {
                            $processedClassrooms[$className] = true;

                            Classroom::updateOrCreate(
                                [
                                    'name' => $className,
                                    'academic_year_id' => $this->parent->getAcademicYearId(),
                                ],
                                [
                                    'is_active' => true,
                                ]
                            );
                        }

                        // 3. Simpan Assignment
                        if ($className) {
                            $classroom = Classroom::where('name', $className)
                                ->where('academic_year_id', $this->parent->getAcademicYearId())
                                ->first();

                            if ($classroom) {
                                // Akhiri assignment aktif sebelumnya
                                $activeAssignment = StudentClassAssignment::where('student_id', $student->id)
                                    ->whereNull('end_date')
                                    ->first();

                                if ($activeAssignment) {
                                    $activeAssignment->update(['end_date' => now()->subDay()]);
                                }

                                // Buat assignment baru
                                StudentClassAssignment::updateOrCreate(
                                    [
                                        'student_id' => $student->id,
                                        'classroom_id' => $classroom->id,
                                        'start_date' => now()->toDateString(),
                                    ],
                                    [
                                        'end_date' => null,
                                    ]
                                );
                            }
                        }
                    }
                }
            },
        ];
    }

    // 👇 Auto-deactivate students not in import
    // public function __destruct()
    // {
    //     $importedIds = $this->getImportedStudentIds();
    //     if (!empty($importedIds)) {
    //         Student::whereNotIn('student_id', $importedIds)
    //             ->where('is_active', true)
    //             ->update(['is_active' => false]);
    //     }
    // }
}