<?php

namespace Tests\Feature\Manager;

use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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
            'code' => 'MNU-001', 'category_id' => $kategori->id,
            'name' => 'Nasi Goreng Ayam', 'price' => 25000,
        ]);

        $this->esTeh = Menu::factory()->create([
            'code' => 'MNU-002', 'category_id' => $kategori->id,
            'name' => 'Es Teh Manis', 'price' => 8000,
        ]);
    }

    /** Membuat satu transaksi lunas lengkap dengan itemnya. */
    private function transaksiLunas(Menu $menu, int $qty, ?CarbonInterface $waktu = null): Transaction
    {
        $waktu ??= now();
        $total = $menu->price * $qty;

        $transaksi = Transaction::factory()->create([
            'user_id' => $this->kasir->id,
            'total_amount' => $total,
            'pay_amount' => $total,
            'change_amount' => 0,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => $waktu,
            'created_at' => $waktu,
        ]);

        TransactionItem::factory()->create([
            'transaction_id' => $transaksi->id,
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'price' => $menu->price,
            'qty' => $qty,
            'subtotal' => $total,
        ]);

        return $transaksi;
    }

    public function test_ringkasan_menghitung_omzet_hari_ini(): void
    {
        $this->transaksiLunas($this->nasiGoreng, 2);   // 50.000
        $this->transaksiLunas($this->esTeh, 3);        // 24.000
        $this->transaksiLunas($this->nasiGoreng, 1, now()->subDays(5)); // di luar hari ini

        $ringkasan = app(DashboardService::class)->summary();

        $this->assertSame(2, $ringkasan['today_count']);
        $this->assertSame(74000, $ringkasan['today_revenue']);
    }

    public function test_transaksi_belum_bayar_tidak_masuk_omzet(): void
    {
        $this->transaksiLunas($this->nasiGoreng, 2); // 50.000

        Transaction::factory()->create([
            'user_id' => $this->kasir->id,
            'total_amount' => 999000,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $ringkasan = app(DashboardService::class)->summary();

        $this->assertSame(1, $ringkasan['today_count']);
        $this->assertSame(50000, $ringkasan['today_revenue']);
        $this->assertSame(1, $ringkasan['pending_count']);
        $this->assertSame(999000, $ringkasan['pending_value']);
    }

    public function test_ringkasan_menghitung_menu_tersedia_dan_habis(): void
    {
        Menu::factory()->soldOut()->create([
            'code' => 'MNU-003',
            'category_id' => $this->nasiGoreng->category_id,
            'name' => 'Pecel Lele',
        ]);

        $ringkasan = app(DashboardService::class)->summary();

        $this->assertSame(2, $ringkasan['menu_available']);
        $this->assertSame(1, $ringkasan['menu_sold_out']);
    }

    public function test_grafik_harian_mengisi_hari_kosong_dengan_nol(): void
    {
        $this->transaksiLunas($this->nasiGoreng, 2, now());              // hari ini, 50.000
        $this->transaksiLunas($this->esTeh, 1, now()->subDays(2));       // 2 hari lalu, 8.000

        $grafik = app(DashboardService::class)->dailyChart(5);

        $this->assertCount(5, $grafik['labels']);
        $this->assertCount(5, $grafik['counts']);
        $this->assertCount(5, $grafik['revenues']);

        // Indeks terakhir = hari ini, indeks 2 = dua hari lalu.
        $this->assertSame(50000, $grafik['revenues'][4]);
        $this->assertSame(1, $grafik['counts'][4]);
        $this->assertSame(8000, $grafik['revenues'][2]);

        // Hari tanpa transaksi tetap muncul bernilai 0.
        $this->assertSame(0, $grafik['revenues'][0]);
        $this->assertSame(0, $grafik['counts'][3]);
    }

    public function test_menu_terlaris_diurutkan_dari_jumlah_porsi(): void
    {
        $this->transaksiLunas($this->esTeh, 10);       // 10 porsi
        $this->transaksiLunas($this->nasiGoreng, 3);   // 3 porsi

        $terlaris = app(DashboardService::class)->bestSellers(5);

        $this->assertSame('Es Teh Manis', $terlaris[0]['name']);
        $this->assertSame(10, $terlaris[0]['qty']);
        $this->assertSame(80000, $terlaris[0]['revenue']);

        $this->assertSame('Nasi Goreng Ayam', $terlaris[1]['name']);
        $this->assertSame(3, $terlaris[1]['qty']);
    }

    public function test_halaman_dashboard_menampilkan_angka_yang_benar(): void
    {
        $this->transaksiLunas($this->nasiGoreng, 2);
        $this->transaksiLunas($this->esTeh, 3);

        $manajer = User::factory()->manager()->create();

        $this->actingAs($manajer)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSee('Rp74.000')
            ->assertSee('Transaksi Hari Ini');
    }

    public function test_filter_daftar_transaksi_manajer(): void
    {
        $this->transaksiLunas($this->nasiGoreng, 1);

        Transaction::factory()->create([
            'user_id' => $this->kasir->id,
            'invoice_number' => 'KSRK-9999-001',
            'total_amount' => 12000,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $manajer = User::factory()->manager()->create();

        $this->actingAs($manajer)
            ->get(route('manager.transactions.index', ['status' => 'belum_bayar']))
            ->assertOk()
            ->assertSee('KSRK-9999-001');

        $this->actingAs($manajer)
            ->get(route('manager.transactions.index', ['cari' => 'KSRK-9999']))
            ->assertOk()
            ->assertSee('KSRK-9999-001');
    }
}
