<?php

namespace App\Services;

use App\Enums\MenuStatus;
use App\Models\ActivityLog;
use App\Models\Menu;
use App\Models\Transaction;
use App\Support\Clock;
use App\Support\SqlDialect;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Semua angka dashboard dihitung di satu tempat agar konsisten.
 *
 * Dua aturan yang berlaku untuk SEMUA angka di sini:
 *  1. Omzet hanya dihitung dari transaksi berstatus Lunas.
 *  2. Sebuah transaksi masuk hitungan hari `created_at` (hari pesanan dibuat),
 *     bukan `paid_at`. Halaman kasir memakai patokan yang sama, sehingga angka
 *     di dashboard manajer dan di layar kasir tidak pernah berbeda.
 */
class DashboardService
{
    /** Kartu ringkasan baris pertama. */
    public function summary(): array
    {
        $today = Transaction::query()->whereDate('created_at', today());
        $monthStart = CarbonImmutable::today()->startOfMonth();

        $month = Transaction::query()->where('created_at', '>=', $monthStart);

        return [
            'today_count' => (clone $today)->paid()->count(),
            'today_revenue' => (int) (clone $today)->paid()->sum('total_amount'),
            'month_count' => (clone $month)->paid()->count(),
            'month_revenue' => (int) (clone $month)->paid()->sum('total_amount'),
            'month_label' => Clock::monthYear(now()),
            'pending_count' => Transaction::query()->unpaid()->count(),
            'pending_value' => (int) Transaction::query()->unpaid()->sum('total_amount'),
            'menu_available' => Menu::query()->where('status', MenuStatus::Available)->count(),
            'menu_sold_out' => Menu::query()->where('status', MenuStatus::SoldOut)->count(),
        ];
    }

    /**
     * Grafik batang transaksi & omzet per hari untuk N hari terakhir.
     * Hari tanpa transaksi tetap muncul dengan nilai 0 agar grafik tidak bolong.
     */
    public function dailyChart(int $days = 8): array
    {
        $start = CarbonImmutable::today()->subDays($days - 1);
        $dateExpression = SqlDialect::dateOf('created_at');

        $rows = Transaction::query()
            ->paid()
            ->where('created_at', '>=', $start->startOfDay())
            ->selectRaw("{$dateExpression} as day, COUNT(*) as total_count, SUM(total_amount) as revenue")
            ->groupByRaw($dateExpression)
            ->get()
            // PostgreSQL mengembalikan tanggal sebagai objek/teks "Y-m-d H:i:s"; disamakan dulu.
            ->keyBy(fn ($row) => Carbon::parse($row->day)->toDateString());

        $labels = [];
        $countSeries = [];
        $revenueSeries = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->addDays($i);
            $row = $rows->get($date->toDateString());

            $labels[] = Clock::shortDate($date);
            $countSeries[] = (int) ($row->total_count ?? 0);
            $revenueSeries[] = (int) ($row->revenue ?? 0);
        }

        return [
            'labels' => $labels,
            'counts' => $countSeries,
            'revenues' => $revenueSeries,
        ];
    }

    /** Menu terlaris berdasarkan jumlah porsi terjual pada transaksi Lunas. */
    public function bestSellers(int $limit = 5, int $days = 30): array
    {
        $start = CarbonImmutable::today()->subDays($days - 1)->startOfDay();

        return DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.payment_status', 'lunas')
            ->where('transactions.created_at', '>=', $start)
            ->groupBy('transaction_items.menu_name')
            ->selectRaw('transaction_items.menu_name as name, SUM(transaction_items.qty) as qty, SUM(transaction_items.subtotal) as revenue')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'qty' => (int) $row->qty,
                'revenue' => (int) $row->revenue,
            ])
            ->all();
    }

    /** Ringkasan aktivitas terbaru untuk panel kanan dashboard. */
    public function recentActivity(int $limit = 6): array
    {
        return ActivityLog::query()
            ->with('user:id,name,role')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'time' => $log->created_at->format('H:i'),
                'date' => Clock::date($log->created_at),
                'category' => $log->category,
                'user' => $log->user
                    ? $log->user->name.' ('.$log->user->role->label().')'
                    : 'Sistem',
                'detail' => $log->detail,
                'status' => $log->status,
            ])
            ->all();
    }
}
