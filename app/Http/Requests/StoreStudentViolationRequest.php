<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentViolationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:255',
            'counselor_id' => 'nullable|exists:users,id',
            'follow_up' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'student_id.required' => 'Siswa harus dipilih.',
            'student_id.exists' => 'Siswa tidak ditemukan.',
            'violation_type_id.required' => 'Jenis pelanggaran harus dipilih.',
            'violation_type_id.exists' => 'Jenis pelanggaran tidak ditemukan.',
            'violation_date.required' => 'Tanggal pelanggaran harus dipilih.',
            'violation_date.date' => 'Format tanggal tidak valid.',
            'violation_date.before_or_equal' => 'Tanggal pelanggaran tidak boleh lebih dari hari ini.',
            'notes.max' => 'Keterangan tidak boleh lebih dari :max karakter.',
            'counselor_id.exists' => 'Guru BK tidak ditemukan.',
            'follow_up.max' => 'Tindak lanjut tidak boleh lebih dari :max karakter.',
        ];
    }
}