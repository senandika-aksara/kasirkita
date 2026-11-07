<?php

namespace Tests\Unit;

use App\Support\SqlDialect;
use PHPUnit\Framework\TestCase;

class SqlDialectTest extends TestCase
{
    public function test_urutan_natural_menempelkan_arah_ke_kedua_bagian(): void
    {
        // "LENGTH(c), c DESC" salah karena DESC hanya kena bagian terakhir.
        $this->assertSame('LENGTH(code) DESC, code DESC', SqlDialect::naturalOrder('code', 'DESC'));
        $this->assertSame('LENGTH(code) ASC, code ASC', SqlDialect::naturalOrder('code'));
    }

    public function test_arah_urutan_tidak_dikenal_dianggap_asc(): void
    {
        $this->assertSame('LENGTH(code) ASC, code ASC', SqlDialect::naturalOrder('code', 'bukan-arah'));
    }

    public function test_urutan_enum_memakai_case_yang_berlaku_di_mysql_dan_postgres(): void
    {
        $this->assertSame(
            "CASE role WHEN 'superadmin' THEN 0 WHEN 'manager' THEN 1 WHEN 'kasir' THEN 2 ELSE 3 END",
            SqlDialect::orderByValues('role', ['superadmin', 'manager', 'kasir'])
        );
    }

    public function test_tanda_kutip_pada_nilai_enum_di_escape(): void
    {
        $this->assertStringContainsString("WHEN 'bu''kan' THEN 0", SqlDialect::orderByValues('x', ["bu'kan"]));
    }
}
