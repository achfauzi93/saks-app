<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Dapatkan user id jika ada, misal dari route parameter 'user'
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email,' . $userId,
            ],
            // password wajib kalau create, nullable kalau update
            'password' => $userId
                ? ['nullable', 'string', 'min:3']
                : ['required', 'string', 'min:3'],

            // pastikan 'role' atau 'roles' sesuai input di form
            'role' => ['required', 'exists:roles,id'],  // jika single role
            // atau
            //'roles' => ['required', 'array', 'exists:roles,id'], // jika multiple roles
        ];
    }


    public function messages()
    {
        return [
            'name.required' => 'Nama harus diisi',
            'name.string' => 'Nama harus berupa string',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah digunakan',
            'email.string' => 'Email harus berupa string',
            'email.email' => 'Email tidak valid',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter',
            'password.required' => 'Password harus diisi',
            'password.string' => 'Password harus berupa string',
            'password.min' => 'Password minimal 3 karakter',
            'role.required' => 'Role harus dipilih',
            'role.exists' => 'Role tidak ditemukan',

        ];
    }
}