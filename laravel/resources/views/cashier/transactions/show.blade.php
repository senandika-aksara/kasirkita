<x-layouts.app title="Detail Transaksi" heading="Detail Transaksi">

    <div class="mx-auto max-w-4xl space-y-4">

        <x-card padding="p-0">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line bg-nav-100 px-4 py-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-nav-700">Detail Transaksi</p>
                    <p class="tabular text-xl font-extrabold tracking-tight text-ink">{{ $transaction->invoice_number }}</p>
                </div>
                <x-status-badge :status="$transaction->payment_status" class="text-sm" />
            </div>

            <dl class="grid gap-4 p-4 sm:grid-cols-4">
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Tanggal &amp; Waktu</dt>
                    <dd class="tabular mt-0.5 text-sm font-semibold">{{ \App\Support\Clock::dateTime($transaction->created_at) }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">No. Meja</dt>
                    <dd class="mt-0.5 text-sm font-semibold">
                        {{ $transaction->table_number ? 'Meja '.$transaction->table_number : 'Bawa pulang' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Kasir</dt>
                    <dd class="mt-0.5 text-sm font-semibold">{{ $transaction->user?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Waktu Lunas</dt>
                    <dd class="tabular mt-0.5 text-sm font-semibold">{{ \App\Support\Clock::dateTime($transaction->paid_at) }}</dd>
                </div>
            </dl>
        </x-card>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">

            <x-card title="Rincian Menu" padding="p-0" class="min-w-0">
                <div class="overflow-x-auto scrollbar-slim">
                    <table class="w-full min-w-[460px] text-sm">
                        <thead>
                            <tr class="border-b border-line bg-surface-2 text-left text-[11px] font-bold uppercase tracking-wider text-muted">
                                <th scope="col" class="px-4 py-2.5">Menu</th>
                                <th scope="col" class="px-4 py-2.5 text-center">Qty</th>
                                <th scope="col" class="px-4 py-2.5 text-right">Harga Satuan</th>
                                <th scope="col" class="px-4 py-2.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line-2">
                            @foreach ($transaction->items as $item)
                                <tr>
                                    <th scope="row" class="px-4 py-2.5 text-left font-semibold">
                                        {{ $item->menu_name }}
                                        @if ($item->menu?->code)
                                            <span class="block text-xs font-normal text-muted">{{ $item->menu->code }}</span>
                                        @endif
                                    </th>
                                    <td class="tabular px-4 py-2.5 text-center">{{ $item->qty }}</td>
                                    <td class="px-4 py-2.5 text-right"><x-money :value="$item->price" /></td>
                                    <td class="px-4 py-2.5 text-right font-bold"><x-money :value="$item->subtotal" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-line bg-bg">
                                <th scope="row" colspan="3" class="px-4 py-3 text-right text-sm font-extrabold uppercase">
                                    Total Pesanan
                                </th>
                                <td class="px-4 py-3 text-right text-base font-extrabold">
                                    <x-money :value="$transaction->total_amount" />
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>

            <div class="space-y-4">
                <div class="rounded-xl border border-nav-600/30 bg-nav-100 p-4 text-center">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-nav-700">Total Bayar</p>
                    <p class="tabular mt-1 text-3xl font-extrabold text-nav-800">
                        {{ \App\Support\Money::format($transaction->total_amount) }}
                    </p>

                    @if ($transaction->isPaid())
                        <hr class="my-3 border-nav-600/20">
                        <dl class="space-y-1 text-left text-sm">
                            <div class="flex justify-between">
                                <dt class="text-nav-700">Uang dibayar</dt>
                                <dd class="tabular font-bold"><x-money :value="$transaction->pay_amount" /></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-nav-700">Kembalian</dt>
                                <dd class="tabular font-bold"><x-money :value="$transaction->change_amount" /></dd>
                            </div>
                        </dl>
                    @endif
                </div>

                <div class="space-y-2">
                    @if ($transaction->isPaid())
                        <x-button :href="route('cashier.transactions.receipt', $transaction)"
                                  variant="nav" size="lg" class="w-full" target="_blank">
                            <x-icon name="printer" class="h-5 w-5" />
                            Cetak Struk
                        </x-button>
                    @else
                        <form method="POST" action="{{ route('cashier.transactions.pay', $transaction) }}" class="space-y-2">
                            @csrf
                            <x-input label="Jumlah uang dibayar" name="pay_amount" inputmode="numeric"
                                     placeholder="Kosongkan bila uang pas" class="text-right" />
                            <x-button type="submit" variant="primary" size="lg" class="w-full">
                                <x-icon name="cash" class="h-5 w-5" />
                                Tandai Lunas
                            </x-button>
                        </form>

                        <form method="POST" action="{{ route('cashier.transactions.destroy', $transaction) }}"
                              x-data="{ confirming: false }" @submit="confirming = false">
                            @csrf
                            @method('DELETE')
                            <x-button type="button" variant="outline-danger" size="md" class="w-full"
                                      x-show="!confirming" @click="confirming = true">
                                <x-icon name="trash" class="h-4 w-4" />
                                Hapus Transaksi
                            </x-button>
                            <div x-cloak x-show="confirming" class="space-y-2 rounded-lg border border-danger-600/30 bg-danger-100 p-3">
                                <p class="text-xs font-medium text-danger-700">
                                    Hapus transaksi {{ $transaction->invoice_number }}? Tindakan ini tidak dapat dibatalkan.
                                </p>
                                <div class="flex gap-2">
                                    <x-button type="button" variant="secondary" size="sm" class="flex-1" @click="confirming = false">
                                        Batal
                                    </x-button>
                                    <x-button type="submit" variant="danger" size="sm" class="flex-1">Hapus</x-button>
                                </div>
                            </div>
                        </form>
                    @endif

                    <x-button :href="route('cashier.transactions.index')" variant="secondary" size="md" class="w-full">
                        Kembali ke Daftar Transaksi
                    </x-button>
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
