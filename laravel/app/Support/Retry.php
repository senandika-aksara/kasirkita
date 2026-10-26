<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Throwable;

/**
 * Nomor urut harian (invoice) dan kode menu dihitung dengan "baca nomor terakhir,
 * tambah satu". Pola itu bisa balapan bila dua petugas menyimpan bersamaan.
 *
 * Di MySQL `lockForUpdate()` masih menahan lewat gap lock, tetapi PostgreSQL
 * (Supabase) tidak mengunci apa pun bila query SELECT tidak mencocokkan baris —
 * persis yang terjadi pada transaksi pertama setiap hari. Daripada mengandalkan
 * perilaku kunci yang berbeda antar basis data, operasinya cukup diulang saat
 * indeks unik menolak nomor yang kadung dipakai pihak lain.
 */
class Retry
{
    /** SQLSTATE pelanggaran indeks unik: 23000 (MySQL), 23505 (PostgreSQL). */
    private const UNIQUE_VIOLATION = ['23000', '23505'];

    /**
     * @template T
     *
     * @param  callable(): T  $operation
     * @return T
     */
    public static function onDuplicate(callable $operation, int $attempts = 4): mixed
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $operation();
            } catch (QueryException $e) {
                if ($attempt >= $attempts || ! self::isDuplicate($e)) {
                    throw $e;
                }

                // Jeda singkat & acak supaya dua proses tidak mencoba ulang berbarengan.
                usleep(random_int(5_000, 25_000));
            }
        }
    }

    private static function isDuplicate(Throwable $e): bool
    {
        return in_array((string) $e->getCode(), self::UNIQUE_VIOLATION, true);
    }
}
