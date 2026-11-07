<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cashier\StoreTransactionRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(private readonly TransactionService $transactions) {}

    public function create(Request $request): View
    {
        $categories = Category::query()->ordered()->get(['id', 'name']);

        // Menu habis tetap ditampilkan dengan label, tetapi tidak bisa dipilih.
        $menus = Menu::query()
            ->with('category:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Menu $menu) => [
                'id' => $menu->id,
                'code' => $menu->code,
                'name' => $menu->name,
                'price' => $menu->price,
                'category_id' => $menu->category_id,
                'category' => $menu->category?->name,
                'available' => $menu->isAvailable(),
                'image' => $menu->imageUrl(),
            ]);

        $latest = Transaction::query()
            ->whereDate('created_at', today())
            ->latest('created_at')
            ->take(3)
            ->get(['id', 'invoice_number', 'table_number', 'payment_status']);

        return view('cashier.pos', [
            'categories' => $categories,
            'menus' => $menus,
            'latestTransactions' => $latest,
            'nextInvoice' => $this->transactions->nextInvoiceNumber(),
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $transaction = $this->transactions->store(
            cashier: $request->user(),
            items: $request->orderItems(),
            tableNumber: $request->input('table_number'),
            markPaid: $request->markPaid(),
            payAmount: $request->input('pay_amount'),
        );

        ActivityLog::record(
            $request->user(),
            'Order',
            'Memproses transaksi '.$transaction->invoice_number,
            $transaction->isPaid() ? 'Selesai' : 'Pending',
        );

        $message = $transaction->isPaid()
            ? 'Transaksi '.$transaction->invoice_number.' tersimpan dan ditandai LUNAS. Kembalian: '
                .Money::format($transaction->change_amount).'.'
            : 'Transaksi '.$transaction->invoice_number.' tersimpan dengan status BELUM BAYAR.';

        return redirect()
            ->route('cashier.transactions.index')
            ->with('success', $message)
            ->with('highlight', $transaction->id);
    }
}
