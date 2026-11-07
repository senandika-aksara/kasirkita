<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Clock
{
    private const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    private const MONTHS = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
    ];

    private const MONTHS_LONG = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    /** RABU, 21 OKT 2026, 14:30 — dipakai di bar navigasi. */
    public static function longNow(): string
    {
        $now = now();

        return mb_strtoupper(
            self::DAYS[(int) $now->dayOfWeek].', '
            .$now->day.' '.self::MONTHS[(int) $now->month].' '.$now->year.', '
            .$now->format('H:i')
        );
    }

    /** 21 Okt 2026 14:05 */
    public static function dateTime(?CarbonInterface $date): string
    {
        if (! $date) {
            return '—';
        }

        return $date->day.' '.self::MONTHS[(int) $date->month].' '.$date->year.' '.$date->format('H:i');
    }

    /** 21 Okt 2026 */
    public static function date(?CarbonInterface $date): string
    {
        if (! $date) {
            return '—';
        }

        return $date->day.' '.self::MONTHS[(int) $date->month].' '.$date->year;
    }

    /** 21 Oktober 2026 */
    public static function dateLong(?CarbonInterface $date): string
    {
        if (! $date) {
            return '—';
        }

        return $date->day.' '.self::MONTHS_LONG[(int) $date->month].' '.$date->year;
    }

    /** Oktober 2026 */
    public static function monthYear(?CarbonInterface $date): string
    {
        $date ??= now();

        return self::MONTHS_LONG[(int) $date->month].' '.$date->year;
    }

    /** 21 Okt — label sumbu grafik. */
    public static function shortDate(CarbonInterface $date): string
    {
        return $date->day.' '.self::MONTHS[(int) $date->month];
    }
}
