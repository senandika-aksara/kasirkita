<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Retry;
use App\Support\SqlDialect;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    /**
     * Menyimpan transaksi baru beserta rincian itemnya dalam satu transaksi database.
     *
     * @param  array<int, array{menu_id: int, qty: int}>  $items
     */
    public function store(User $cashier, array $items, ?string $tableNumber, bool $markPaid, ?int $payAmount = null): Transaction
    {
        $normalized = $this->normalizeItems($items);

        if ($normalized === []) {
            throw ValidationException::withMessages([
                'items' => 'Pesanan harus berisi minimal satu menu.',
            ]);
        }

        // Nomor invoice bisa bentrok bila dua kasir menyimpan berbarengan; simpan ulang
        // dengan nomor berikutnya alih-alih memulangkan 500. Lihat App\Support\Retry.
        return Retry::onDuplicate(fn () => DB::transaction(function () use ($cashier, $normalized, $tableNumber, $markPaid, $payAmount) {
            // Harga diambil ulang dari database; nilai dari browser tidak dipercaya.
            $menus = Menu::query()
                ->whereIn('id', array_keys($normalized))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $rows = [];
            $total = 0;

            foreach ($normalized as $menuId => $qty) {
                $menu = $menus->get($menuId);

                if (! $menu) {
                    throw ValidationException::withMessages([
                        'items' => 'Ada menu yang sudah tidak tersedia di katalog. Muat ulang halaman.',
                    ]);
                }

                if (! $menu->isAvailable()) {
                    throw ValidationException::withMessages([
                        'items' => 'Menu "'.$menu->name.'" sedang habis dan tidak bisa dipesan.',
                    ]);
                }

                $subtotal = $menu->price * $qty;
                $total += $subtotal;

                $rows[] = [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'price' => $menu->price,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            $paid = 0;
            $change = 0;

            if ($markPaid) {
                // Jumlah uang kosong berarti pelanggan membayar uang pas.
                $paid = $payAmount ?? $total;

                if ($paid < $total) {
                    throw ValidationException::withMessages([
                        'pay_amount' => 'Jumlah uang yang dibayar kurang dari total transaksi.',
                    ]);
                }

                $change = $paid - $total;
            }

            $transaction = Transaction::create([
                'invoice_number' => $this->nextInvoiceNumber(),
                'table_number' => $tableNumber,
                'user_id' => $cashier->id,
                'total_amount' => $total,
                'pay_amount' => $paid,
                'change_amount' => $change,
                'payment_status' => $markPaid ? PaymentStatus::Paid : PaymentStatus::Unpaid,
                'paid_at' => $markPaid ? now() : null,
            ]);

            $transaction->items()->createMany($rows);

            return $transaction->load('items');
        }));
    }

    /**
     * Melunasi transaksi yang masih berstatus Belum Bayar.
     * Dikunci agar dua kasir tidak bisa melunasi transaksi yang sama bersamaan.
     */
    public function markAsPaid(Transaction $transaction, User $cashier, ?int $payAmount = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $cashier, $payAmount) {
            /** @var Transaction $fresh */
            $fresh = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($fresh->isPaid()) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Transaksi ini sudah berstatus Lunas.',
                ]);
            }

            $paid = $payAmount ?? $fresh->total_amount;

            if ($paid < $fresh->total_amount) {
                throw ValidationException::withMessages([
                    'pay_amount' => 'Jumlah uang yang dibayar kurang dari total transaksi.',
                ]);
            }

            $fresh->update([
                'user_id' => $cashier->id,
                'pay_amount' => $paid,
                'change_amount' => $paid - $fresh->total_amount,
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ]);

            return $fresh->refresh();
        });
    }

    /**
     * Transaksi yang sudah Lunas tidak boleh dihapus.
     *
     * Status dibaca ulang di bawah kunci baris, bukan dari model yang dimuat
     * di awal request: tanpa itu kasir lain bisa melunasi transaksi ini di
     * sela-sela permintaan dan transaksi lunas ikut terhapus.
     */
    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            /** @var Transaction $fresh */
            $fresh = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($fresh->isPaid()) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Transaksi yang sudah Lunas tidak dapat dihapus.',
                ]);
            }

            $fresh->delete();
        });
    }

    /** Nomor transaksi berurutan per hari: KSRK-MMDD-NNN. */
    public function nextInvoiceNumber(?CarbonInterface $date = null): string
    {
        $date ??= now();
        $prefix = 'KSRK-'.$date->format('md').'-';

        // Urutan teks biasa menaruh KSRK-mmdd-999 di atas KSRK-mmdd-1000;
        // naturalOrder mengurutkan panjang dulu sehingga nomor ke-1000 dst. tetap benar.
        $last = Transaction::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByRaw(SqlDialect::naturalOrder('invoice_number', 'DESC'))
            ->lockForUpdate()
            ->value('invoice_number');

        // substr(-3) salah begitu urutan mencapai 4 digit: "KSRK-1025-1000" terbaca "000".
        $next = $last ? ((int) Str::afterLast($last, '-')) + 1 : 1;

        return $prefix.sprintf('%03d', $next);
    }

    /**
     * Menggabungkan baris item dengan menu yang sama dan membuang qty tidak valid.
     *
     * @param  array<int, array{menu_id: int|string, qty: int|string}>  $items
     * @return array<int, int> menu_id => qty
     */
    private function normalizeItems(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            $menuId = (int) ($item['menu_id'] ?? 0);
            $qty = (int) ($item['qty'] ?? 0);

            if ($menuId <= 0 || $qty <= 0) {
                continue;
            }

            $result[$menuId] = min(99, ($result[$menuId] ?? 0) + $qty);
        }

        return $result;
    }
}
