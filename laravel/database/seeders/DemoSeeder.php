<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data contoh untuk keperluan uji coba & demo — JANGAN dijalankan di lingkungan produksi.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Password tiap akun contoh dibuat acak dan dicetak sekali ke layar.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DemoSeeder tidak boleh dijalankan di lingkungan produksi.');

            return;
        }

        $staff = [
            ['name' => 'Ahmad Subagjo', 'username' => 'ahmad.mgr', 'role' => UserRole::Manager, 'status' => UserStatus::Active],
            ['name' => 'Nurul Azizah', 'username' => 'nurul.ksr', 'role' => UserRole::Cashier, 'status' => UserStatus::Active],
            ['name' => 'Budi Santoso', 'username' => 'budi.ksr', 'role' => UserRole::Cashier, 'status' => UserStatus::Active],
            ['name' => 'Siti Nurjanah', 'username' => 'siti.ksr', 'role' => UserRole::Cashier, 'status' => UserStatus::Inactive],
        ];

        $created = [];

        foreach ($staff as $person) {
            if (User::query()->where('username', $person['username'])->exists()) {
                continue;
            }

            $password = Str::password(14, symbols: false);
            User::create($person + ['password' => $password]);
            $created[] = [$person['username'], $password];
        }

        $this->call([
            CategorySeeder::class,
            MenuSeeder::class,
            DemoTransactionSeeder::class,
        ]);

        if ($created !== []) {
            $this->command?->warn('Akun contoh dibuat — catat sekarang, tidak ditampilkan lagi:');

            foreach ($created as [$username, $password]) {
                $this->command?->line("  {$username} : {$password}");
            }
        }
    }
}
