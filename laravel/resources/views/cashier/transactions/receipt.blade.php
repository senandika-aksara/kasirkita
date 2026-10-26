<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $transaction->invoice_number }} — KasirKita</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg p-4 antialiased">

    <div class="no-print mx-auto mb-4 flex max-w-sm flex-wrap items-center justify-between gap-2">
        <x-button :href="route('cashier.transactions.index')" variant="secondary" size="md">Kembali</x-button>
        <x-button type="button" variant="primary" size="md" onclick="window.print()">
            <x-icon name="printer" class="h-5 w-5" />
            Cetak Struk
        </x-button>
    </div>

    {{-- Lebar 80mm agar pas di printer struk; bisa juga disimpan sebagai PDF dari dialog cetak. --}}
    <main class="print-area mx-auto w-[80mm] max-w-full border border-line bg-white p-4 text-[12px] leading-snug text-black shadow-sm">

        <header class="border-b border-dashed border-black pb-2 text-center">
            <p class="text-base font-extrabold tracking-tight">KASIR KITA</p>
            <p class="text-[11px]">Aplikasi Kasir Sederhana</p>
            <p class="mt-1 text-[11px]">UMKM Kuliner</p>
        </header>

        <dl class="tabular space-y-0.5 border-b border-dashed border-black py-2 text-[11px]">
            <div class="flex justify-between">
                <dt>No. Transaksi</dt>
                <dd class="font-bold">{{ $transaction->invoice_number }}</dd>
            </div>
            <div class="flex justify-between">
                <dt>Tanggal</dt>
                <dd>{{ \App\Support\Clock::dateTime($transaction->paid_at ?? $transaction->created_at) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt>Kasir</dt>
                <dd>{{ $transaction->user?->name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt>No. Meja</dt>
                <dd>{{ $transaction->table_number ? 'Meja '.$transaction->table_number : 'Bawa pulang' }}</dd>
            </div>
        </dl>

        <table class="tabular w-full border-b border-dashed border-black py-2 text-[11px]">
            <caption class="sr-only">Rincian pesanan</caption>
            <tbody>
                @foreach ($transaction->items as $item)
                    <tr>
                        <td colspan="2" class="pt-1.5 font-bold">{{ $item->menu_name }}</td>
                    </tr>
                    <tr>
                        <td class="pb-1">{{ $item->qty }} x {{ \App\Support\Money::format($item->price) }}</td>
                        <td class="pb-1 text-right">{{ \App\Support\Money::format($item->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <dl class="tabular space-y-0.5 border-b border-dashed border-black py-2 text-[11px]">
            <div class="flex justify-between text-sm font-extrabold">
                <dt>TOTAL</dt>
                <dd>{{ \App\Support\Money::format($transaction->total_amount) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt>Status</dt>
                <dd class="font-bold">{{ \Illuminate\Support\Str::upper($transaction->payment_status->label()) }}</dd>
            </div>
            @if ($transaction->isPaid())
                <div class="flex justify-between">
                    <dt>Tunai</dt>
                    <dd>{{ \App\Support\Money::format($transaction->pay_amount) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>Kembalian</dt>
                    <dd>{{ \App\Support\Money::format($transaction->change_amount) }}</dd>
                </div>
            @endif
        </dl>

        <footer class="pt-2 text-center text-[11px]">
            <p class="font-bold">Terima kasih atas kunjungan Anda</p>
            <p>Simpan struk ini sebagai bukti pembayaran</p>
            <p class="mt-1 text-[10px]">Dicetak: {{ \App\Support\Clock::dateTime(now()) }}</p>
        </footer>
    </main>

</body>
</html>
