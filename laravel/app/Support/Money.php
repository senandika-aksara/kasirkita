<?php

namespace App\Support;

class Money
{
    /** Rp1.234.567 — rupiah bulat, tanpa desimal. */
    public static function format(int $rupiah): string
    {
        return 'Rp'.number_format($rupiah, 0, ',', '.');
    }

    /** Membaca masukan pengguna ("35.000", "Rp35.000", "35000") menjadi integer. */
    public static function parse(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        // Masukan bisa berupa array/objek bila nama field dikarang (mis. "pay_amount[]=1").
        // Tanpa penjagaan ini (string) melempar ErrorException dan request berakhir 500,
        // bukan 422 dari validator.
        if (! is_scalar($value)) {
            return 0;
        }

        $digits = preg_replace('/\D/', '', (string) $value);

        return $digits === '' ? 0 : (int) $digits;
    }
}
