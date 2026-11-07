<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_dapat_dibuka(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke sistem')
            ->assertSee('Username')
            ->assertSee('Password');
    }

    public function test_kasir_masuk_lalu_diarahkan_ke_daftar_transaksi(): void
    {
        $kasir = User::factory()->cashier()->create([
            'username' => 'nurul.ksr',
            'password' => 'rahasia123',
        ]);

        $this->post('/login', [
            'username' => 'nurul.ksr',
            'password' => 'rahasia123',
        ])->assertRedirect(route('cashier.transactions.index'));

        $this->assertAuthenticatedAs($kasir);
    }

    public function test_manajer_masuk_lalu_diarahkan_ke_dashboard(): void
    {
        User::factory()->manager()->create([
            'username' => 'ahmad.mgr',
            'password' => 'rahasia123',
        ]);

        $this->post('/login', [
            'username' => 'ahmad.mgr',
            'password' => 'rahasia123',
        ])->assertRedirect(route('manager.dashboard'));
    }

    public function test_password_salah_ditolak(): void
    {
        User::factory()->cashier()->create([
            'username' => 'nurul.ksr',
            'password' => 'rahasia123',
        ]);

        $this->post('/login', [
            'username' => 'nurul.ksr',
            'password' => 'password-salah',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_akun_non_aktif_tidak_bisa_masuk(): void
    {
        User::factory()->cashier()->inactive()->create([
            'username' => 'siti.ksr',
            'password' => 'rahasia123',
        ]);

        $this->post('/login', [
            'username' => 'siti.ksr',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_login_dibatasi_lima_percobaan_gagal(): void
    {
        RateLimiter::clear('nurul.ksr|127.0.0.1');

        User::factory()->cashier()->create([
            'username' => 'nurul.ksr',
            'password' => 'rahasia123',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['username' => 'nurul.ksr', 'password' => 'salah']);
        }

        // Percobaan keenam ditolak walaupun passwordnya benar.
        $this->post('/login', ['username' => 'nurul.ksr', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_pengguna_dapat_keluar(): void
    {
        $user = User::factory()->cashier()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_akun_yang_dinonaktifkan_saat_sesi_berjalan_dikeluarkan(): void
    {
        $kasir = User::factory()->cashier()->create();

        $this->actingAs($kasir)->get(route('cashier.transactions.index'))->assertOk();

        $kasir->update(['status' => 'non_aktif']);

        $this->get(route('cashier.transactions.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
