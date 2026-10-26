<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    /** Halaman cetak struk; dicetak lewat dialog cetak browser. */
    public function __invoke(Transaction $transaction): View
    {
        $transaction->load(['items', 'user:id,name']);

        return view('cashier.transactions.receipt', ['transaction' => $transaction]);
    }
}
