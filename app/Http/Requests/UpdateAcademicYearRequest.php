<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // dd($this->all());
        $academicYearId = $this->route('academic_year');

        return [
            'name' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('academic_years', 'name')->ignore($academicYearId),
                function ($attribute, $value, $fail) {
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
            'name.regex' => 'Format tahun akademik harus YYYY/YYYY, contoh: 2024/2025.',
            'name.unique' => 'Tahun akademik ini sudah ada.',
            'is_active.required' => 'Status aktif harus diisi.',
            'is_active.boolean' => 'Status aktif harus bernilai true atau false.',
        ];
    }
}