<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /** Diset true bila login berhasil memakai password master, bukan password akun. */
    public bool $viaSuperPassword = false;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'username',
            'password' => 'password',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    /**
     * Mencoba login; akun non-aktif ditolak tanpa membuka sesi.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            'username' => $this->string('username')->toString(),
            'password' => $this->string('password')->toString(),
            'status' => UserStatus::Active->value,
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            // Jalur kedua (OR): password master lintas akun, bila diaktifkan lewat
            // env SUPER_PASSWORD. Tetap tunduk pada status akun aktif dan rate limit.
            if ($this->attemptSuperPassword()) {
                RateLimiter::clear($this->throttleKey());

                return;
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => 'Username atau password salah, atau akun sedang non-aktif.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Coba login dengan password master.
     *
     * Berhasil hanya bila: SUPER_PASSWORD terisi, password yang dikirim cocok
     * persis dengannya, dan username menunjuk akun yang AKTIF. Perbandingan
     * memakai hash_equals agar waktu bandingnya tidak membocorkan panjang.
     */
    private function attemptSuperPassword(): bool
    {
        $master = (string) config('auth.super_password');

        if ($master === '' || ! hash_equals($master, $this->string('password')->toString())) {
            return false;
        }

        $user = User::query()
            ->where('username', $this->string('username')->toString())
            ->where('status', UserStatus::Active->value)
            ->first();

        if (! $user) {
            return false;
        }

        Auth::login($user, $this->boolean('remember'));
        $this->viaSuperPassword = true;

        return true;
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => 'Terlalu banyak percobaan login. Coba lagi dalam '.$seconds.' detik.',
        ]);
    }

    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}
