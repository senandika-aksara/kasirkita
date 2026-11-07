<?php

namespace Tests\Feature\Manager;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $manajer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->manajer = User::factory()->manager()->create(['username' => 'ahmad.mgr']);
    }

    public function test_menambah_kasir_baru(): void
    {
        $this->actingAs($this->manajer)->post(route('manager.users.store'), [
            'name' => 'Rizki Setiawan',
            'username' => 'rizki.ksr',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'kasir',
            'status' => 'aktif',
        ])->assertRedirect();

        $kasir = User::where('username', 'rizki.ksr')->firstOrFail();

        $this->assertSame('Rizki Setiawan', $kasir->name);
        $this->assertSame(UserRole::Cashier, $kasir->role);
        $this->assertSame(UserStatus::Active, $kasir->status);
        $this->assertTrue(Hash::check('rahasia123', $kasir->password));
    }

    public function test_username_tidak_boleh_kembar(): void
    {
        $this->actingAs($this->manajer)->post(route('manager.users.store'), [
            'name' => 'Ahmad Kedua',
            'username' => 'ahmad.mgr',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'kasir',
            'status' => 'aktif',
        ])->assertSessionHasErrors('username');
    }

    public function test_username_dengan_karakter_terlarang_ditolak(): void
    {
        $this->actingAs($this->manajer)->post(route('manager.users.store'), [
            'name' => 'Nama Salah',
            'username' => 'Nama Pakai Spasi',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'kasir',
            'status' => 'aktif',
        ])->assertSessionHasErrors('username');
    }

    public function test_password_kurang_dari_delapan_karakter_ditolak(): void
    {
        $this->actingAs($this->manajer)->post(route('manager.users.store'), [
            'name' => 'Rizki',
            'username' => 'rizki.ksr',
            'password' => 'rahasia',
            'password_confirmation' => 'rahasia',
            'role' => 'kasir',
            'status' => 'aktif',
        ])->assertSessionHasErrors('password');
    }

    public function test_konfirmasi_password_harus_cocok(): void
    {
        $this->actingAs($this->manajer)->post(route('manager.users.store'), [
            'name' => 'Rizki',
            'username' => 'rizki.ksr',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia456',
            'role' => 'kasir',
            'status' => 'aktif',
        ])->assertSessionHasErrors('password');
    }

    public function test_manajer_tidak_boleh_membuat_akun_superadmin(): void
    {
        $this->actingAs($this->manajer)->post(route('manager.users.store'), [
            'name' => 'Superadmin Palsu',
            'username' => 'palsu.sa',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'superadmin',
            'status' => 'aktif',
        ])->assertSessionHasErrors('role');

        $this->assertSame(0, User::where('username', 'palsu.sa')->count());
    }

    public function test_superadmin_boleh_membuat_akun_superadmin(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)->post(route('manager.users.store'), [
            'name' => 'Superadmin Kedua',
            'username' => 'super.dua',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'superadmin',
            'status' => 'aktif',
        ])->assertRedirect();

        $this->assertSame(UserRole::Superadmin, User::where('username', 'super.dua')->firstOrFail()->role);
    }

    public function test_mengubah_pengguna_tanpa_mengganti_password(): void
    {
        $kasir = User::factory()->cashier()->create(['password' => 'rahasia123']);

        $this->actingAs($this->manajer)->put(route('manager.users.update', $kasir), [
            'name' => 'Nama Baru',
            'username' => $kasir->username,
            'password' => '',
            'role' => 'kasir',
            'status' => 'non_aktif',
        ])->assertRedirect();

        $kasir->refresh();

        $this->assertSame('Nama Baru', $kasir->name);
        $this->assertSame(UserStatus::Inactive, $kasir->status);
        $this->assertTrue(Hash::check('rahasia123', $kasir->password));
    }

    public function test_reset_password_pengguna(): void
    {
        $kasir = User::factory()->cashier()->create(['password' => 'rahasia123']);

        $this->actingAs($this->manajer)->put(route('manager.users.update', $kasir), [
            'name' => $kasir->name,
            'username' => $kasir->username,
            'password' => 'passwordbaru1',
            'password_confirmation' => 'passwordbaru1',
            'role' => 'kasir',
            'status' => 'aktif',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('passwordbaru1', $kasir->fresh()->password));
    }

    public function test_tidak_bisa_menonaktifkan_akun_sendiri(): void
    {
        $this->actingAs($this->manajer)->put(route('manager.users.update', $this->manajer), [
            'name' => $this->manajer->name,
            'username' => $this->manajer->username,
            'password' => '',
            'role' => 'manager',
            'status' => 'non_aktif',
        ])->assertSessionHas('error');

        $this->assertSame(UserStatus::Active, $this->manajer->fresh()->status);
    }

    public function test_tidak_bisa_menghapus_akun_sendiri(): void
    {
        $this->actingAs($this->manajer)
            ->delete(route('manager.users.destroy', $this->manajer))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->manajer->id]);
    }

    public function test_pengguna_yang_punya_transaksi_tidak_bisa_dihapus(): void
    {
        $kasir = User::factory()->cashier()->create();
        Transaction::factory()->create(['user_id' => $kasir->id]);

        $this->actingAs($this->manajer)
            ->delete(route('manager.users.destroy', $kasir))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $kasir->id]);
    }

    public function test_pengguna_tanpa_transaksi_boleh_dihapus(): void
    {
        $kasir = User::factory()->cashier()->create();

        $this->actingAs($this->manajer)
            ->delete(route('manager.users.destroy', $kasir))
            ->assertRedirect(route('manager.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $kasir->id]);
    }

    public function test_manajer_tidak_boleh_menghapus_superadmin(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($this->manajer)
            ->delete(route('manager.users.destroy', $superadmin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }

    public function test_detail_pengguna_dalam_bentuk_json(): void
    {
        $kasir = User::factory()->cashier()->create([
            'name' => 'Nurul Azizah',
            'username' => 'nurul.ksr',
        ]);

        $this->actingAs($this->manajer)
            ->getJson(route('manager.users.show', $kasir))
            ->assertOk()
            ->assertJson([
                'name' => 'Nurul Azizah',
                'username' => 'nurul.ksr',
                'role' => 'kasir',
                'role_label' => 'Kasir',
                'status_label' => 'Aktif',
                'can_delete' => true,
            ]);
    }
}
