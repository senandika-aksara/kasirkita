<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Beberapa ekspresi SQL berbeda antara MySQL (XAMPP lokal) dan
 * PostgreSQL (Supabase). Perbedaan itu dikumpulkan di sini supaya
 * query di Service tetap bersih dan aplikasi bisa jalan di keduanya.
 */
class SqlDialect
{
    public static function driver(?string $connection = null): string
    {
        return DB::connection($connection)->getDriverName();
    }

    /** Mengambil bagian tanggal dari kolom timestamp. */
    public static function dateOf(string $column): string
    {
        return self::driver() === 'pgsql'
            ? "CAST({$column} AS DATE)"
            : "DATE({$column})";
    }

    /**
     * Mengurutkan kolom teks berakhiran angka (mis. MNU-007) secara numerik.
     * LENGTH diurutkan lebih dulu agar MNU-1000 tetap dianggap lebih besar
     * dari MNU-999. Arah urutan harus ditempelkan ke KEDUA bagian, karena
     * "ORDER BY LENGTH(c), c DESC" hanya membalik bagian terakhir.
     */
    public static function naturalOrder(string $column, string $direction = 'ASC'): string
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return "LENGTH({$column}) {$direction}, {$column} {$direction}";
    }

    /**
     * Urutan khusus untuk kolom bertipe enum; MySQL punya FIELD(),
     * PostgreSQL tidak, jadi dipakai CASE yang berlaku di keduanya.
     *
     * @param  array<int, string>  $values
     */
    public static function orderByValues(string $column, array $values): string
    {
        $cases = [];

        foreach (array_values($values) as $index => $value) {
            // Nilai berasal dari Enum milik aplikasi, bukan masukan pengguna.
            $escaped = str_replace("'", "''", $value);
            $cases[] = "WHEN '{$escaped}' THEN {$index}";
        }

        return 'CASE '.$column.' '.implode(' ', $cases).' ELSE '.count($cases).' END';
    }
}
