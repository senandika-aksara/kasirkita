<?php

namespace Tests\Feature\Cashier;

use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;

    private Menu $nasiGoreng;

    private Menu $esTeh;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasir = User::factory()->cashier()->create();
        $kategori = Category::factory()->create(['name' => 'Makanan']);

        $this->nasiGoreng = Menu::factory()->create([
            'code' => 'MNU-001',
            'category_id' => $kategori->id,
            'name' => 'Nasi Goreng Ayam',
            'price' => 25000,
        ]);

        $this->esTeh = Menu::factory()->create([
            'code' => 'MNU-002',
            'category_id' => $kategori->id,
            'name' => 'Es Teh Manis',
            'price' => 8000,
        ]);
    }

    public function test_menyimpan_transaksi_lunas_dan_menghitung_kembalian(): void
    {
        // 2 x 25.000 + 3 x 8.000 = 74.000; bayar 100.000 -> kembali 26.000
        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'table_number' => '5',
            'payment_status' => 'lunas',
            'pay_amount' => '100.000',
            'items' => [
                ['menu_id' => $this->nasiGoreng->id, 'qty' => 2],
                ['menu_id' => $this->esTeh->id, 'qty' => 3],
            ],
        ])->assertRedirect(route('cashier.transactions.index'));

        $transaksi = Transaction::latest('id')->firstOrFail();

        $this->assertSame(74000, $transaksi->total_amount);
        $this->assertSame(100000, $transaksi->pay_amount);
        $this->assertSame(26000, $transaksi->change_amount);
        $this->assertSame(PaymentStatus::Paid, $transaksi->payment_status);
        $this->assertSame('5', $transaksi->table_number);
        $this->assertNotNull($transaksi->paid_at);
        $this->assertCount(2, $transaksi->items);
    }

    public function test_jumlah_uang_kosong_dianggap_uang_pas(): void
    {
        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'payment_status' => 'lunas',
            'pay_amount' => '',
            'items' => [['menu_id' => $this->esTeh->id, 'qty' => 1]],
        ]);

        $transaksi = Transaction::latest('id')->firstOrFail();

        $this->assertSame(8000, $transaksi->pay_amount);
        $this->assertSame(0, $transaksi->change_amount);
    }

    public function test_transaksi_belum_bayar_tidak_mencatat_pembayaran(): void
    {
        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'payment_status' => 'belum_bayar',
            'items' => [['menu_id' => $this->nasiGoreng->id, 'qty' => 1]],
        ]);

        $transaksi = Transaction::latest('id')->firstOrFail();

        $this->assertSame(PaymentStatus::Unpaid, $transaksi->payment_status);
        $this->assertSame(0, $transaksi->pay_amount);
        $this->assertNull($transaksi->paid_at);
    }

    public function test_uang_kurang_dari_total_ditolak(): void
    {
        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'payment_status' => 'lunas',
            'pay_amount' => 10000,
            'items' => [['menu_id' => $this->nasiGoreng->id, 'qty' => 2]],
        ])->assertSessionHasErrors('pay_amount');

        $this->assertSame(0, Transaction::count());
    }

    public function test_menu_habis_tidak_bisa_dipesan(): void
    {
        $habis = Menu::factory()->soldOut()->create([
            'code' => 'MNU-003',
            'category_id' => $this->nasiGoreng->category_id,
            'name' => 'Pecel Lele',
            'price' => 26000,
        ]);

        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'payment_status' => 'lunas',
            'items' => [['menu_id' => $habis->id, 'qty' => 1]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Transaction::count());
    }

    public function test_pesanan_kosong_ditolak(): void
    {
        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'payment_status' => 'lunas',
            'items' => [],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Transaction::count());
    }

    public function test_harga_diambil_dari_database_bukan_dari_browser(): void
    {
        // Kiriman browser menyertakan harga palsu; server harus mengabaikannya.
        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'payment_status' => 'lunas',
            'items' => [['menu_id' => $this->nasiGoreng->id, 'qty' => 1, 'price' => 1]],
        ]);

        $transaksi = Transaction::latest('id')->firstOrFail();

        $this->assertSame(25000, $transaksi->total_amount);
        $this->assertSame(25000, $transaksi->items->first()->price);
    }

    public function test_item_menyimpan_salinan_nama_dan_harga(): void
    {
        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'payment_status' => 'lunas',
            'items' => [['menu_id' => $this->nasiGoreng->id, 'qty' => 2]],
        ]);

        $item = Transaction::latest('id')->firstOrFail()->items->first();

        $this->assertSame('Nasi Goreng Ayam', $item->menu_name);
        $this->assertSame(25000, $item->price);
        $this->assertSame(50000, $item->subtotal);

        // Harga menu berubah, transaksi lama harus tetap.
        $this->nasiGoreng->update(['price' => 30000]);

        $this->assertSame(25000, $item->fresh()->price);
    }

    public function test_nomor_transaksi_berurutan_per_hari(): void
    {
        foreach (range(1, 3) as $ignored) {
            $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
                'payment_status' => 'lunas',
                'items' => [['menu_id' => $this->esTeh->id, 'qty' => 1]],
            ]);
        }

        $nomor = Transaction::orderBy('id')->pluck('invoice_number')->all();
        $prefix = 'KSRK-'.now()->format('md').'-';

        $this->assertSame([$prefix.'001', $prefix.'002', $prefix.'003'], $nomor);
    }

    public function test_transaksi_belum_bayar_dapat_dilunasi(): void
    {
        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'payment_status' => 'belum_bayar',
            'items' => [['menu_id' => $this->nasiGoreng->id, 'qty' => 2]],
        ]);

        $transaksi = Transaction::latest('id')->firstOrFail();

        $this->actingAs($this->kasir)
            ->post(route('cashier.transactions.pay', $transaksi), ['pay_amount' => 50000])
            ->assertRedirect(route('cashier.transactions.index'));

        $transaksi->refresh();

        $this->assertSame(PaymentStatus::Paid, $transaksi->payment_status);
        $this->assertSame(50000, $transaksi->pay_amount);
        $this->assertSame(0, $transaksi->change_amount);
        $this->assertNotNull($transaksi->paid_at);
    }

    public function test_transaksi_lunas_tidak_bisa_dilunasi_dua_kali(): void
    {
        $transaksi = Transaction::factory()->create([
            'user_id' => $this->kasir->id,
            'total_amount' => 25000,
            'pay_amount' => 25000,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($this->kasir)
            ->post(route('cashier.transactions.pay', $transaksi), ['pay_amount' => 100000])
            ->assertSessionHasErrors('payment_status');

        $this->assertSame(25000, $transaksi->fresh()->pay_amount);
    }

    public function test_transaksi_lunas_tidak_bisa_dihapus(): void
    {
        $transaksi = Transaction::factory()->create([
            'user_id' => $this->kasir->id,
            'total_amount' => 25000,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($this->kasir)
            ->delete(route('cashier.transactions.destroy', $transaksi))
            ->assertSessionHasErrors('payment_status');

        $this->assertDatabaseHas('transactions', ['id' => $transaksi->id]);
    }

    public function test_transaksi_belum_bayar_boleh_dihapus(): void
    {
        $transaksi = Transaction::factory()->create([
            'user_id' => $this->kasir->id,
            'total_amount' => 25000,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $this->actingAs($this->kasir)
            ->delete(route('cashier.transactions.destroy', $transaksi))
            ->assertRedirect();

        $this->assertDatabaseMissing('transactions', ['id' => $transaksi->id]);
    }

    public function test_halaman_struk_dapat_dibuka(): void
    {
        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'payment_status' => 'lunas',
            'pay_amount' => 30000,
            'items' => [['menu_id' => $this->nasiGoreng->id, 'qty' => 1]],
        ]);

        $transaksi = Transaction::latest('id')->firstOrFail();

        $this->actingAs($this->kasir)
            ->get(route('cashier.transactions.receipt', $transaksi))
            ->assertOk()
            ->assertSee($transaksi->invoice_number)
            ->assertSee('Rp5.000'); // kembalian
    }

    public function test_baris_menu_yang_sama_digabung(): void
    {
        $this->actingAs($this->kasir)->post(route('cashier.transactions.store'), [
            'payment_status' => 'lunas',
            'items' => [
                ['menu_id' => $this->esTeh->id, 'qty' => 2],
                ['menu_id' => $this->esTeh->id, 'qty' => 3],
            ],
        ]);

        $transaksi = Transaction::latest('id')->firstOrFail();

        $this->assertCount(1, $transaksi->items);
        $this->assertSame(5, $transaksi->items->first()->qty);
        $this->assertSame(40000, $transaksi->total_amount);
    }
}
