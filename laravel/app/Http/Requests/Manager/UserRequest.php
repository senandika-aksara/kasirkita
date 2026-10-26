<?php

namespace App\Http\Requests\Manager;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isManagerial();
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $isCreating = $userId === null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required', 'string', 'min:3', 'max:50',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            // Saat mengubah, password boleh dikosongkan agar tidak diganti.
            'password' => [$isCreating ? 'required' : 'nullable', 'string', 'min:8', 'max:72', 'confirmed'],
            'role' => ['required', Rule::in($this->allowedRoles())],
            'status' => ['required', 'in:aktif,non_aktif'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, titik, garis bawah, dan strip.',
            'username.unique' => 'Username tersebut sudah dipakai pengguna lain.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.in' => 'Anda tidak berhak memberikan level akses tersebut.',
            'photo.max' => 'Ukuran foto pengguna maksimal 1 MB.',
        ];
    }

    /** Hanya superadmin yang boleh membuat/mengubah akun superadmin. */
    private function allowedRoles(): array
    {
        if ($this->user()?->isSuperadmin()) {
            return [UserRole::Superadmin->value, UserRole::Manager->value, UserRole::Cashier->value];
        }

        return [UserRole::Manager->value, UserRole::Cashier->value];
    }
}
