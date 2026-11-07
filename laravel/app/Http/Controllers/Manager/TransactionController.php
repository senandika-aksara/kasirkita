<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DashboardService;
use App\Support\Clock;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /** Mengubah masukan query jadi tanggal; nilai tak sah dianggap tidak memfilter apa pun. */
    private static function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return rescue(fn () => CarbonImmutable::parse($value), null, false);
    }

    /** Riwayat seluruh transaksi dengan filter status, kasir, tanggal, dan pencarian. */
    public function index(Request $request, DashboardService $dashboard): View
    {
        $status = in_array($request->query('status'), ['lunas', 'belum_bayar'], true)
            ? $request->query('status')
            : 'semua';

        $cashierId = $request->integer('kasir') ?: null;
        // date() memanggil Carbon::parse pada masukan mentah; "?dari=abc" melempar
        // InvalidFormatException dan memulangkan 500. Tanggal tak sah diabaikan saja.
        $from = self::parseDate($request->query('dari'));
        $to = self::parseDate($request->query('sampai'));
        $search = trim((string) $request->query('cari'));

        $base = Transaction::query()
            ->when($cashierId, fn ($query) => $query->where('user_id', $cashierId))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('invoice_number', 'like', '%'.$search.'%')
                        ->orWhere('table_number', 'like', '%'.$search.'%');
                });
            });

        $counts = [
            'semua' => (clone $base)->count(),
            'belum_bayar' => (clone $base)->unpaid()->count(),
            'lunas' => (clone $base)->paid()->count(),
        ];

        $transactions = (clone $base)
            ->when($status !== 'semua', fn ($query) => $query->where('payment_status', $status))
            ->with(['items:id,transaction_id,menu_name,qty', 'user:id,name'])
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('manager.transactions.index', [
            'transactions' => $transactions,
            'counts' => $counts,
            'status' => $status,
            'cashiers' => User::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'kasir' => $cashierId,
                'dari' => $from?->toDateString(),
                'sampai' => $to?->toDateString(),
                'cari' => $search,
            ],
            'revenue' => (int) (clone $base)->paid()->sum('total_amount'),
            'activities' => $dashboard->recentActivity(4),
        ]);
    }

    /** Dipakai oleh pop-up detail transaksi di halaman daftar. */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load(['items', 'user:id,name']);

        return response()->json([
            'invoice_number' => $transaction->invoice_number,
            'created_at' => Clock::dateTime($transaction->created_at),
            'paid_at' => Clock::dateTime($transaction->paid_at),
            'table' => $transaction->table_number ? 'Meja '.$transaction->table_number : 'Bawa pulang',
            'cashier' => $transaction->user?->name ?? '—',
            'status' => $transaction->payment_status->label(),
            'is_paid' => $transaction->isPaid(),
            'total' => Money::format($transaction->total_amount),
            'pay_amount' => Money::format($transaction->pay_amount),
            'change_amount' => Money::format($transaction->change_amount),
            'receipt_url' => route('cashier.transactions.receipt', $transaction),
            'items' => $transaction->items->map(fn ($item) => [
                'name' => $item->menu_name,
                'qty' => $item->qty,
                'price' => Money::format($item->price),
                'subtotal' => Money::format($item->subtotal),
            ]),
        ]);
    }
}
