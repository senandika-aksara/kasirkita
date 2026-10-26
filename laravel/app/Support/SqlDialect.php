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

    /**
     * Menyelaraskan sequence auto-increment PostgreSQL dengan nilai id terbesar
     * di tabel. Diperlukan setelah menyisipkan baris dengan id EKSPLISIT (mis.
     * seeder data contoh): MySQL menaikkan AUTO_INCREMENT sendiri saat id manual
     * dimasukkan, tetapi PostgreSQL tidak menggeser sequence-nya — sehingga INSERT
     * berikutnya memakai id yang sudah ada dan gagal dengan "duplicate key value
     * violates unique constraint". Di MySQL fungsi ini tidak melakukan apa-apa.
     */
    public static function resetSequence(string $table, string $column = 'id'): void
    {
        if (self::driver() !== 'pgsql') {
            return;
        }

        $sequence = DB::selectOne(
            'SELECT pg_get_serial_sequence(?, ?) AS seq',
            [$table, $column]
        )?->seq;

        if (! $sequence) {
            return;
        }

        $max = (int) (DB::table($table)->max($column) ?? 0);

        // Parameter ketiga true => nextval berikutnya = max + 1.
        DB::select('SELECT setval(?, ?, true)', [$sequence, max($max, 1)]);
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
