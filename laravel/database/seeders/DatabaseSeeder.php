<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seed bawaan = siap produksi: satu akun pemilik usaha + kategori menu.
 * Data contoh (menu, kasir, transaksi 30 hari) ada di DemoSeeder dan
 * dijalankan terpisah: php artisan db:seed --class=DemoSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
