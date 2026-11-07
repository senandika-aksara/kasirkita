<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string}> */
    public static function halamanManajer(): array
    {
        return [
            'dashboard' => ['manager.dashboard'],
            'transaksi' => ['manager.transactions.index'],
            'menu' => ['manager.menus.index'],
            'pengguna' => ['manager.users.index'],
        ];
    }

    #[DataProvider('halamanManajer')]
    public function test_kasir_tidak_boleh_membuka_panel_manajer(string $route): void
    {
        $kasir = User::factory()->cashier()->create();

        $this->actingAs($kasir)->get(route($route))->assertForbidden();
    }

    #[DataProvider('halamanManajer')]
    public function test_manajer_boleh_membuka_panel_manajer(string $route): void
    {
        $manajer = User::factory()->manager()->create();

        $this->actingAs($manajer)->get(route($route))->assertOk();
    }

    #[DataProvider('halamanManajer')]
    public function test_superadmin_boleh_membuka_panel_manajer(string $route): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)->get(route($route))->assertOk();
    }

    public function test_manajer_juga_boleh_memakai_panel_kasir(): void
    {
        $manajer = User::factory()->manager()->create();

        $this->actingAs($manajer)->get(route('cashier.transactions.index'))->assertOk();
        $this->actingAs($manajer)->get(route('cashier.pos.create'))->assertOk();
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('cashier.transactions.index'))->assertRedirect(route('login'));
        $this->get(route('manager.dashboard'))->assertRedirect(route('login'));
    }

    public function test_halaman_depan_mengarahkan_sesuai_peran(): void
    {
        $this->get('/')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->cashier()->create())
            ->get('/')->assertRedirect(route('cashier.transactions.index'));

        $this->actingAs(User::factory()->manager()->create())
            ->get('/')->assertRedirect(route('manager.dashboard'));
    }
}
