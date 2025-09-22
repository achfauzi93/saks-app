<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Atur sesuai kebutuhan autentikasi/otorisasi
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('academic_years', 'name'),
                'regex:/^\d{4}\/\d{4}$/',
                function ($attribute, $value, $fail) {
                    // Pastikan value bisa dipecah menjadi 2 tahun
                    $parts = explode('/', $value);
                    if (count($parts) !== 2) {
                        return $fail('Format tahun akademik tidak valid.');
                    }

                    [$start, $end] = $parts;

                    if (!is_numeric($start) || !is_numeric($end)) {
                        return $fail('Tahun akademik harus berupa angka.');
                    }

                    if ((int)$end !== (int)$start + 1) {
                        return $fail('Tahun kedua harus tepat satu tahun setelah tahun pertama.');
                    }
                },
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tahun akademik wajib diisi.',
            'name.string' => 'Tahun akademik harus berupa teks.',
            'name.unique' => 'Tahun akademik ini sudah ada.',
            'name.regex' => 'Format tahun akademik harus YYYY/YYYY, contoh: 2024/2025.',
            'is_active.required' => 'Status aktif harus diisi.',
            'is_active.boolean' => 'Status aktif harus bernilai true atau false.',
        ];
    }
}