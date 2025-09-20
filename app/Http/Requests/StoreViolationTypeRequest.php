<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreViolationTypeRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:violation_types,name',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis pelanggaran wajib diisi.',
            'name.string' => 'Nama jenis pelanggaran harus berupa teks.',
            'name.max' => 'Nama jenis pelanggaran tidak boleh lebih dari 255 karakter.',
            'name.unique' => 'Nama jenis pelanggaran sudah ada.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'is_active.boolean' => 'Status aktif harus berupa nilai boolean.',
        ];
    }
}