<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    /** @return array<string, array{0: int, 1: string}> */
    public static function nilaiFormat(): array
    {
        return [
            'nol' => [0, 'Rp0'],
            'ribuan' => [8000, 'Rp8.000'],
            'puluhan ribu' => [25000, 'Rp25.000'],
            'ratusan ribu' => [154080, 'Rp154.080'],
            'jutaan' => [1234567, 'Rp1.234.567'],
            'puluhan juta' => [60819000, 'Rp60.819.000'],
        ];
    }

    #[DataProvider('nilaiFormat')]
    public function test_format_rupiah(int $rupiah, string $harapan): void
    {
        $this->assertSame($harapan, Money::format($rupiah));
    }

    /** @return array<string, array{0: mixed, 1: int}> */
    public static function nilaiParse(): array
    {
        return [
            'integer' => [35000, 35000],
            'teks polos' => ['35000', 35000],
            'pakai titik' => ['35.000', 35000],
            'pakai Rp' => ['Rp35.000', 35000],
            'pakai spasi' => ['Rp 35.000', 35000],
            'pakai koma' => ['35,000', 35000],
            'kosong' => ['', 0],
            'null' => [null, 0],
            'huruf saja' => ['abc', 0],
        ];
    }

    #[DataProvider('nilaiParse')]
    public function test_parse_masukan_pengguna(mixed $masukan, int $harapan): void
    {
        $this->assertSame($harapan, Money::parse($masukan));
    }
}
