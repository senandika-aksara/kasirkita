<?php

namespace Tests\Feature\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Password master lintas akun (env SUPER_PASSWORD).
 *
 * Dipakai bersama password asli tiap akun lewat kondisi OR: login berhasil bila
 * password cocok dengan password akun ATAU dengan password master yang aktif.
 */
class SuperPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('nurul.ksr|127.0.0.1');
    }

    public function test_password_master_bisa_masuk_ke_akun_aktif_mana_pun(): void
    {
        config(['auth.super_password' => 'kunci-induk-rahasia']);

        $kasir = User::factory()->cashier()->create([
            'username' => 'nurul.ksr',
            'password' => 'password-asli-kasir',
        ]);

        $this->post('/login', [
            'username' => 'nurul.ksr',
            'password' => 'kunci-induk-rahasia',
        ])->assertRedirect(route('cashier.transactions.index'));

        $this->assertAuthenticatedAs($kasir);
    }

    public function test_password_asli_akun_tetap_berlaku_saat_master_aktif(): void
    {
        config(['auth.super_password' => 'kunci-induk-rahasia']);

        $kasir = User::factory()->cashier()->create([
            'username' => 'nurul.ksr',
            'password' => 'password-asli-kasir',
        ]);

        $this->post('/login', [
            'username' => 'nurul.ksr',
            'password' => 'password-asli-kasir',
        ])->assertRedirect(route('cashier.transactions.index'));

        $this->assertAuthenticatedAs($kasir);
    }

    public function test_pemakaian_master_dicatat_khusus_di_log(): void
    {
        config(['auth.super_password' => 'kunci-induk-rahasia']);

        User::factory()->cashier()->create([
            'username' => 'nurul.ksr',
            'password' => 'password-asli-kasir',
        ]);

        $this->post('/login', [
            'username' => 'nurul.ksr',
            'password' => 'kunci-induk-rahasia',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'category' => 'Login',
            'detail' => 'Masuk sebagai Kasir memakai password master',
        ]);
    }

    public function test_master_kosong_berarti_fitur_mati(): void
    {
        config(['auth.super_password' => '']);

        User::factory()->cashier()->create([
            'username' => 'nurul.ksr',
            'password' => 'password-asli-kasir',
        ]);

        // Password apa pun selain password asli harus ditolak.
        $this->post('/login', [
            'username' => 'nurul.ksr',
            'password' => 'tebakan-sembarang',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_master_tidak_bisa_masuk_ke_akun_nonaktif(): void
    {
        config(['auth.super_password' => 'kunci-induk-rahasia']);

        User::factory()->cashier()->inactive()->create([
            'username' => 'siti.ksr',
            'password' => 'password-asli-siti',
        ]);

        $this->post('/login', [
            'username' => 'siti.ksr',
            'password' => 'kunci-induk-rahasia',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_master_salah_tetap_ditolak(): void
    {
        config(['auth.super_password' => 'kunci-induk-rahasia']);

        User::factory()->cashier()->create([
            'username' => 'nurul.ksr',
            'password' => 'password-asli-kasir',
        ]);

        $this->post('/login', [
            'username' => 'nurul.ksr',
            'password' => 'kunci-induk-SALAH',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }
}
