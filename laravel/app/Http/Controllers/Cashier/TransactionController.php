<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cashier\PayTransactionRequest;
use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactions) {}

    /** Daftar transaksi hari ini dengan tab Semua / Belum Bayar / Lunas. */
    public function index(Request $request): View
    {
        $filter = in_array($request->query('status'), ['lunas', 'belum_bayar'], true)
            ? $request->query('status')
            : 'semua';

        $base = Transaction::query()->whereDate('created_at', today());

        $counts = [
            'semua' => (clone $base)->count(),
            'belum_bayar' => (clone $base)->unpaid()->count(),
            'lunas' => (clone $base)->paid()->count(),
        ];

        $transactions = (clone $base)
            ->when($filter !== 'semua', fn ($query) => $query->where('payment_status', $filter))
            ->with(['items:id,transaction_id,menu_name,qty', 'user:id,name'])
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('cashier.transactions.index', [
            'transactions' => $transactions,
            'counts' => $counts,
            'filter' => $filter,
            'todayRevenue' => (clone $base)->paid()->sum('total_amount'),
            'highlight' => session('highlight'),
        ]);
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['items.menu:id,code', 'user:id,name']);

        return view('cashier.transactions.show', ['transaction' => $transaction]);
    }

    public function pay(PayTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $transaction = $this->transactions->markAsPaid(
            transaction: $transaction,
            cashier: $request->user(),
            payAmount: $request->input('pay_amount'),
        );

        ActivityLog::record(
            $request->user(),
            'Order',
            'Melunasi transaksi '.$transaction->invoice_number,
            'Selesai',
        );

        return redirect()
            ->route('cashier.transactions.index')
            ->with('success', 'Transaksi '.$transaction->invoice_number.' ditandai LUNAS. Kembalian: '
                .Money::format($transaction->change_amount).'.')
            ->with('highlight', $transaction->id);
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $invoice = $transaction->invoice_number;

        $this->transactions->delete($transaction);

        ActivityLog::record($request->user(), 'Order', 'Menghapus transaksi '.$invoice, 'Dihapus');

        return redirect()
            ->route('cashier.transactions.index', ['status' => PaymentStatus::Unpaid->value])
            ->with('success', 'Transaksi '.$invoice.' telah dihapus.');
    }
}
