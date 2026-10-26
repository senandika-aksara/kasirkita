<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder produksi: hanya membuat satu akun pemilik usaha (admin).
 * Akun kasir dibuat sendiri oleh pemilik lewat halaman Kelola Pengguna.
 *
 * Username & password diambil dari variabel lingkungan ADMIN_USERNAME dan
 * ADMIN_PASSWORD. Bila ADMIN_PASSWORD kosong, dibuatkan password acak yang
 * dicetak sekali ke layar — tidak ada password bawaan yang bisa ditebak.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');

        if (User::query()->where('username', $username)->exists()) {
            $this->command?->info("Akun admin \"{$username}\" sudah ada — dilewati.");

            return;
        }

        $password = env('ADMIN_PASSWORD');
        $generated = $password === null || $password === '';

        if ($generated) {
            $password = Str::password(16, symbols: false);
        }

        User::create([
            'name' => env('ADMIN_NAME', 'Pemilik Usaha'),
            'username' => $username,
            'password' => $password,
            'role' => UserRole::Superadmin,
            'status' => UserStatus::Active,
        ]);

        if ($generated) {
            $this->command?->warn('Password admin dibuat otomatis — catat sekarang, tidak ditampilkan lagi:');
            $this->command?->line("  username : {$username}");
            $this->command?->line("  password : {$password}");
        } else {
            $this->command?->info("Akun admin \"{$username}\" dibuat dengan password dari ADMIN_PASSWORD.");
        }
    }
}
