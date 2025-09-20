<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    // Counter untuk hitung baris yang diproses
    public static $rowCount = 0;

    public function model(array $row)
    {
        self::$rowCount++;

        return Student::updateOrCreate(
            ['student_id' => $row['student_id']],
            [
                'name' => strtoupper($row['name']),
                'email' => $row['email'] ?? null,
                'phone' => $row['phone'] ?? null,
                'birth_date' => !empty($row['birth_date']) ? $row['birth_date'] : null,
                'address' => $row['address'] ?? null,
                'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date_format:Y-m-d',
            'address' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'student_id.required' => 'NIS/NISN wajib diisi.',
            'name.required' => 'Nama wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        // Biarkan trait handle
    }
}