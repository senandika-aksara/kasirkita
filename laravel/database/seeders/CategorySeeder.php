<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/** Kategori menu adalah data master, bukan data contoh — ikut di-seed saat produksi. */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Makanan', 'Minuman', 'Camilan'] as $sort => $name) {
            Category::updateOrCreate(['name' => $name], ['sort_order' => $sort + 1]);
        }
    }
}
