<?php

namespace Tests\Feature\Manager;

use App\Enums\MenuStatus;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $manajer;

    private Category $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->manajer = User::factory()->manager()->create();
        $this->kategori = Category::factory()->create(['name' => 'Makanan']);
    }

    public function test_menambah_menu_dan_kode_dibuat_otomatis(): void
    {
        $this->actingAs($this->manajer)->post(route('manager.menus.store'), [
            'name' => 'Nasi Goreng Spesial',
            'category_id' => $this->kategori->id,
            'price' => '35.000',
            'status' => 'tersedia',
        ])->assertRedirect();

        $menu = Menu::firstOrFail();

        $this->assertSame('MNU-001', $menu->code);
        $this->assertSame(35000, $menu->price);
        $this->assertSame(MenuStatus::Available, $menu->status);
        $this->assertSame($this->manajer->id, $menu->updated_by);
    }

    public function test_kode_menu_berurutan(): void
    {
        foreach (['Nasi Goreng', 'Es Teh', 'Ayam Geprek'] as $nama) {
            $this->actingAs($this->manajer)->post(route('manager.menus.store'), [
                'name' => $nama,
                'category_id' => $this->kategori->id,
                'price' => 10000,
                'status' => 'tersedia',
            ]);
        }

        $this->assertSame(
            ['MNU-001', 'MNU-002', 'MNU-003'],
            Menu::orderBy('id')->pluck('code')->all()
        );
    }

    public function test_nama_menu_tidak_boleh_kembar(): void
    {
        Menu::factory()->create(['name' => 'Nasi Goreng', 'category_id' => $this->kategori->id]);

        $this->actingAs($this->manajer)->post(route('manager.menus.store'), [
            'name' => 'Nasi Goreng',
            'category_id' => $this->kategori->id,
            'price' => 25000,
            'status' => 'tersedia',
        ])->assertSessionHasErrors('name');

        $this->assertSame(1, Menu::count());
    }

    public function test_harga_nol_ditolak(): void
    {
        $this->actingAs($this->manajer)->post(route('manager.menus.store'), [
            'name' => 'Menu Gratis',
            'category_id' => $this->kategori->id,
            'price' => 0,
            'status' => 'tersedia',
        ])->assertSessionHasErrors('price');
    }

    public function test_mengunggah_foto_menu(): void
    {
        $this->actingAs($this->manajer)->post(route('manager.menus.store'), [
            'name' => 'Nasi Goreng',
            'category_id' => $this->kategori->id,
            'price' => 25000,
            'status' => 'tersedia',
            'image' => UploadedFile::fake()->image('nasgor.jpg', 400, 300),
        ]);

        $menu = Menu::firstOrFail();

        $this->assertNotNull($menu->image);
        Storage::disk('public')->assertExists($menu->image);
    }

    public function test_foto_lebih_dari_satu_mb_ditolak(): void
    {
        $this->actingAs($this->manajer)->post(route('manager.menus.store'), [
            'name' => 'Nasi Goreng',
            'category_id' => $this->kategori->id,
            'price' => 25000,
            'status' => 'tersedia',
            'image' => UploadedFile::fake()->create('besar.jpg', 2048, 'image/jpeg'),
        ])->assertSessionHasErrors('image');
    }

    public function test_mengubah_menu(): void
    {
        $menu = Menu::factory()->create([
            'name' => 'Es Teh',
            'category_id' => $this->kategori->id,
            'price' => 8000,
        ]);

        $this->actingAs($this->manajer)->put(route('manager.menus.update', $menu), [
            'name' => 'Es Teh Manis',
            'category_id' => $this->kategori->id,
            'price' => 10000,
            'status' => 'habis',
        ])->assertRedirect();

        $menu->refresh();

        $this->assertSame('Es Teh Manis', $menu->name);
        $this->assertSame(10000, $menu->price);
        $this->assertSame(MenuStatus::SoldOut, $menu->status);
    }

    public function test_mengganti_foto_menghapus_foto_lama(): void
    {
        $menu = Menu::factory()->create(['category_id' => $this->kategori->id]);

        $this->actingAs($this->manajer)->put(route('manager.menus.update', $menu), [
            'name' => $menu->name,
            'category_id' => $this->kategori->id,
            'price' => $menu->price,
            'status' => 'tersedia',
            'image' => UploadedFile::fake()->image('lama.jpg'),
        ]);

        $fotoLama = $menu->fresh()->image;

        $this->actingAs($this->manajer)->put(route('manager.menus.update', $menu), [
            'name' => $menu->name,
            'category_id' => $this->kategori->id,
            'price' => $menu->price,
            'status' => 'tersedia',
            'image' => UploadedFile::fake()->image('baru.jpg'),
        ]);

        $fotoBaru = $menu->fresh()->image;

        $this->assertNotSame($fotoLama, $fotoBaru);
        Storage::disk('public')->assertMissing($fotoLama);
        Storage::disk('public')->assertExists($fotoBaru);
    }

    public function test_menu_yang_belum_terjual_boleh_dihapus(): void
    {
        $menu = Menu::factory()->create(['category_id' => $this->kategori->id]);

        $this->actingAs($this->manajer)
            ->delete(route('manager.menus.destroy', $menu))
            ->assertRedirect(route('manager.menus.index'));

        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    public function test_menu_yang_sudah_terjual_tidak_boleh_dihapus(): void
    {
        $menu = Menu::factory()->create(['category_id' => $this->kategori->id]);
        $transaksi = Transaction::factory()->create(['user_id' => $this->manajer->id]);

        TransactionItem::factory()->create([
            'transaction_id' => $transaksi->id,
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'price' => $menu->price,
            'qty' => 1,
            'subtotal' => $menu->price,
        ]);

        $this->actingAs($this->manajer)
            ->delete(route('manager.menus.destroy', $menu))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('menus', ['id' => $menu->id]);
    }

    public function test_detail_menu_dalam_bentuk_json(): void
    {
        $menu = Menu::factory()->create([
            'code' => 'MNU-007',
            'name' => 'Ayam Geprek',
            'category_id' => $this->kategori->id,
            'price' => 30000,
        ]);

        $this->actingAs($this->manajer)
            ->getJson(route('manager.menus.show', $menu))
            ->assertOk()
            ->assertJson([
                'code' => 'MNU-007',
                'name' => 'Ayam Geprek',
                'price' => 30000,
                'price_formatted' => 'Rp30.000',
                'category' => 'Makanan',
                'can_delete' => true,
            ]);
    }

    public function test_pencarian_menu_berfungsi(): void
    {
        Menu::factory()->create(['name' => 'Nasi Goreng Ayam', 'category_id' => $this->kategori->id]);
        Menu::factory()->create(['name' => 'Es Teh Manis', 'category_id' => $this->kategori->id]);

        $this->actingAs($this->manajer)
            ->get(route('manager.menus.index', ['cari' => 'Nasi']))
            ->assertOk()
            ->assertSee('Nasi Goreng Ayam')
            ->assertDontSee('Es Teh Manis');
    }

    public function test_kasir_tidak_boleh_mengelola_menu(): void
    {
        $kasir = User::factory()->cashier()->create();

        $this->actingAs($kasir)->post(route('manager.menus.store'), [
            'name' => 'Menu Selundupan',
            'category_id' => $this->kategori->id,
            'price' => 10000,
            'status' => 'tersedia',
        ])->assertForbidden();

        $this->assertSame(0, Menu::count());
    }
}
