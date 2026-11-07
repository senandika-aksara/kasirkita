<x-layouts.app title="Daftar Transaksi" heading="Daftar Transaksi">

    <x-card padding="p-0">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-2 p-5">
            <div>
                <h2 class="text-base font-bold tracking-tight">Daftar Transaksi Hari Ini</h2>
                <p class="text-sm text-muted">{{ \App\Support\Clock::dateLong(now()) }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-xl border border-line bg-surface-2 px-3 py-2 text-right">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-muted">Belum Bayar</p>
                    <p class="tabular text-base font-extrabold text-danger-700">{{ $counts['belum_bayar'] }}</p>
                </div>
                <div class="rounded-xl border border-line bg-surface-2 px-3 py-2 text-right">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-muted">Omzet Hari Ini</p>
                    <p class="tabular text-base font-extrabold text-brand-600">{{ \App\Support\Money::format($todayRevenue) }}</p>
                </div>
                <x-button :href="route('cashier.pos.create')" variant="primary" size="lg">
                    <x-icon name="plus" class="h-5 w-5" />
                    Transaksi Baru
                </x-button>
            </div>
        </div>

        {{-- Tab filter status --}}
        <div class="flex flex-wrap gap-2 border-b border-line-2 p-4" role="tablist" aria-label="Filter status transaksi">
            @php
                $tabs = [
                    'semua' => ['Semua', $counts['semua'], 'nav'],
                    'belum_bayar' => ['Belum Bayar', $counts['belum_bayar'], 'danger'],
                    'lunas' => ['Lunas', $counts['lunas'], 'brand'],
                ];
            @endphp

            @foreach ($tabs as $key => [$label, $count, $tone])
                @php $isActive = $filter === $key; @endphp
                <a href="{{ route('cashier.transactions.index', $key === 'semua' ? [] : ['status' => $key]) }}"
                   role="tab"
                   aria-selected="{{ $isActive ? 'true' : 'false' }}"
                   @class([
                       'inline-flex min-h-11 items-center gap-2 rounded-xl border px-4 text-sm font-semibold transition',
                       'border-nav-700 bg-nav-700 text-white' => $isActive && $tone === 'nav',
                       'border-danger-600 bg-danger-600 text-white' => $isActive && $tone === 'danger',
                       'border-brand-600 bg-brand-600 text-white' => $isActive && $tone === 'brand',
                       'border-line bg-surface text-ink-2 hover:bg-surface-2' => ! $isActive,
                   ])>
                    @if ($tone === 'danger')
                        <x-icon name="warning" class="h-4 w-4" />
                    @elseif ($tone === 'brand')
                        <x-icon name="check" class="h-4 w-4" />
                    @endif
                    {{ $label }}
                    <span class="tabular rounded px-1.5 py-0.5 text-xs {{ $isActive ? 'bg-white/20' : 'bg-bg' }}">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        @if ($transactions->isEmpty())
            <x-empty title="Belum ada transaksi"
                     message="Transaksi yang dicatat hari ini akan muncul di sini.">
                <x-button :href="route('cashier.pos.create')" variant="primary" size="md" class="mt-2">
                    <x-icon name="plus" class="h-5 w-5" />
                    Buat Transaksi Baru
                </x-button>
            </x-empty>
        @else
            <div class="overflow-x-auto scrollbar-slim">
                <table class="w-full min-w-[920px] text-sm">
                    <caption class="sr-only">Daftar transaksi hari ini</caption>
                    <thead>
                        <tr class="border-b border-line bg-surface-2 text-left text-[11px] font-bold uppercase tracking-wider text-muted">
                            <th scope="col" class="px-4 py-3">ID Transaksi</th>
                            <th scope="col" class="px-4 py-3">No. Meja</th>
                            <th scope="col" class="px-4 py-3">Waktu</th>
                            <th scope="col" class="px-4 py-3">Item Pesanan</th>
                            <th scope="col" class="px-4 py-3 text-right">Total Bayar</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-2">
                        @foreach ($transactions as $transaction)
                            <tr @class([
                                'align-top transition hover:bg-surface-2',
                                'bg-brand-100/60' => $highlight == $transaction->id,
                            ])>
                                <th scope="row" class="px-4 py-3 text-left font-bold whitespace-nowrap">
                                    <a href="{{ route('cashier.transactions.show', $transaction) }}" class="hover:text-nav-700 hover:underline">
                                        {{ $transaction->invoice_number }}
                                    </a>
                                </th>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $transaction->table_number ? str_pad($transaction->table_number, 2, '0', STR_PAD_LEFT) : 'Bawa pulang' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $transaction->created_at->format('H:i') }}</td>
                                <td class="px-4 py-3 text-ink-2">{{ $transaction->itemSummary() }}</td>
                                <td class="px-4 py-3 text-right font-bold whitespace-nowrap">
                                    <x-money :value="$transaction->total_amount" />
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$transaction->payment_status" />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        @if ($transaction->isPaid())
                                            <x-button :href="route('cashier.transactions.receipt', $transaction)"
                                                      variant="secondary" size="sm" target="_blank">
                                                <x-icon name="printer" class="h-4 w-4" />
                                                Struk
                                            </x-button>
                                        @else
                                            <x-button type="button" variant="nav" size="sm"
                                                      x-data
                                                      @click="$dispatch('open-modal', {
                                                          name: 'bayar',
                                                          payload: {
                                                              action: '{{ route('cashier.transactions.pay', $transaction) }}',
                                                              invoice: '{{ $transaction->invoice_number }}',
                                                              total: {{ $transaction->total_amount }},
                                                              table: '{{ $transaction->table_number ? 'Meja '.$transaction->table_number : 'Bawa pulang' }}'
                                                          }
                                                      })">
                                                <x-icon name="cash" class="h-4 w-4" />
                                                Bayar
                                            </x-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($transactions->hasPages())
                <div class="border-t border-line-2 p-4">{{ $transactions->links() }}</div>
            @endif
        @endif
    </x-card>

    {{-- Modal pelunasan transaksi Belum Bayar --}}
    <x-modal name="bayar" title="Lunasi Transaksi" maxWidth="max-w-md">
        <form method="POST" :action="data.action"
              x-data="{ payInput: '', submitting: false }"
              {{-- Modal hanya di-toggle (x-show), form tidak dibuat ulang. Tanpa reset ini
                   nominal dari transaksi sebelumnya ikut terbawa saat modal dibuka lagi. --}}
              x-effect="if (! open) { payInput = ''; submitting = false }"
              @submit="submitting = true"
              class="space-y-4">
            @csrf

            <div class="rounded-xl border border-line bg-surface-2 px-3 py-2 text-sm">
                <p class="font-bold" x-text="data.invoice"></p>
                <p class="text-muted" x-text="data.table"></p>
            </div>

            <div class="rounded-lg border border-nav-600/30 bg-nav-100 p-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-nav-800">Total Tagihan</p>
                <p class="tabular text-3xl font-extrabold text-nav-800" x-text="rupiah(data.total)"></p>
            </div>

            <div class="space-y-2">
                <label for="modal-pay" class="block text-xs font-semibold text-ink-2">
                    Jumlah Uang Dibayar
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-bold text-muted">Rp</span>
                    <input id="modal-pay" name="pay_amount" type="text" inputmode="numeric" autofocus
                           x-model="payInput"
                           placeholder="Kosongkan bila uang pas"
                           class="tabular block min-h-11 w-full rounded-lg border border-line bg-surface py-2 pr-3 pl-9 text-right text-base font-bold focus:outline-2 focus:outline-nav-600">
                </div>

                <div class="flex flex-wrap gap-1">
                    <button type="button" @click="payInput = String(data.total)"
                            class="min-h-9 rounded-md border border-line bg-surface px-2 text-xs font-bold text-nav-700 hover:bg-surface-2">
                        Uang pas
                    </button>
                    <template x-for="note in [20000, 50000, 100000]" :key="note">
                        <button type="button" @click="payInput = String(note)"
                                class="tabular min-h-9 rounded-md border border-line bg-surface px-2 text-xs font-bold text-nav-700 hover:bg-surface-2"
                                x-text="rupiah(note)"></button>
                    </template>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-ink-2">Uang Kembalian</p>
                <p class="tabular mt-1 rounded-xl border border-line bg-surface-2 px-3 py-2 text-right text-xl font-extrabold"
                   :class="(payInput !== '' && Number(String(payInput).replace(/\D/g, '')) < data.total) ? 'text-danger-700' : 'text-brand-600'"
                   x-text="(() => {
                       const digits = String(payInput).replace(/\D/g, '');
                       const paid = digits === '' ? data.total : Number(digits);
                       return paid < data.total ? 'Kurang ' + rupiah(data.total - paid) : rupiah(paid - data.total);
                   })()"></p>
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal')">Batal</x-button>
                <x-button type="submit" variant="primary"
                          x-bind:disabled="submitting || (String(payInput).replace(/\D/g, '') !== '' && Number(String(payInput).replace(/\D/g, '')) < data.total)">
                    <span x-text="submitting ? 'Memproses…' : 'Konfirmasi Lunas'">Konfirmasi Lunas</span>
                </x-button>
            </div>
        </form>
    </x-modal>

</x-layouts.app>
