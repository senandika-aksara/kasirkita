<x-layouts.app title="Daftar Transaksi" heading="Daftar Transaksi">

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_280px]">

        <x-card padding="p-0" class="min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-2 p-5">
                <div>
                    <h2 class="text-base font-bold tracking-tight">Daftar Transaksi Penjualan</h2>
                    <p class="tabular text-sm text-muted">
                        Omzet pada filter ini: <strong class="text-brand-600">{{ \App\Support\Money::format($revenue) }}</strong>
                    </p>
                </div>

                <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filter status transaksi">
                    @php
                        $tabs = [
                            'semua' => ['Semua', $counts['semua'], 'nav'],
                            'belum_bayar' => ['Belum Bayar', $counts['belum_bayar'], 'danger'],
                            'lunas' => ['Lunas', $counts['lunas'], 'brand'],
                        ];
                    @endphp

                    @foreach ($tabs as $key => [$label, $count, $tone])
                        @php $isActive = $status === $key; @endphp
                        <a href="{{ route('manager.transactions.index', array_filter($filters + ['status' => $key === 'semua' ? null : $key])) }}"
                           role="tab"
                           aria-selected="{{ $isActive ? 'true' : 'false' }}"
                           @class([
                               'inline-flex min-h-11 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition',
                               'border-nav-700 bg-nav-700 text-white' => $isActive && $tone === 'nav',
                               'border-danger-600 bg-danger-600 text-white' => $isActive && $tone === 'danger',
                               'border-brand-600 bg-brand-600 text-white' => $isActive && $tone === 'brand',
                               'border-line bg-surface text-ink-2 hover:bg-surface-2' => ! $isActive,
                           ])>
                            {{ $label }}
                            <span class="tabular rounded px-1.5 py-0.5 text-xs {{ $isActive ? 'bg-white/20' : 'bg-bg' }}">{{ $count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Filter periode, kasir, pencarian --}}
            <form method="GET" class="grid gap-3 border-b border-line-2 bg-surface-2 p-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
                <input type="hidden" name="status" value="{{ $status }}">

                <x-input label="Cari kode / meja" name="cari" type="search"
                         value="{{ $filters['cari'] }}" placeholder="KSRK-1021-001" />

                <x-input label="Dari tanggal" name="dari" type="date" value="{{ $filters['dari'] }}" />
                <x-input label="Sampai tanggal" name="sampai" type="date" value="{{ $filters['sampai'] }}" />

                <x-select label="Kasir" name="kasir">
                    <option value="">Semua kasir</option>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" @selected($filters['kasir'] == $cashier->id)>
                            {{ $cashier->name }}
                        </option>
                    @endforeach
                </x-select>

                <div class="flex flex-wrap items-end gap-2">
                    <x-button type="submit" variant="nav" size="md" class="flex-1">
                        <x-icon name="search" class="h-4 w-4" />
                        Terapkan
                    </x-button>
                    <x-button :href="route('manager.transactions.index')" variant="secondary" size="md">Reset</x-button>
                </div>
            </form>

            @if ($transactions->isEmpty())
                <x-empty title="Tidak ada transaksi"
                         message="Tidak ada transaksi yang cocok dengan filter yang dipilih." />
            @else
                <div class="overflow-x-auto scrollbar-slim">
                    <table class="w-full min-w-[980px] text-sm">
                        <caption class="sr-only">Daftar transaksi penjualan</caption>
                        <thead>
                            <tr class="border-b border-line bg-surface-2 text-left text-[11px] font-bold uppercase tracking-wider text-muted">
                                <th scope="col" class="px-4 py-3">ID Transaksi</th>
                                <th scope="col" class="px-4 py-3">Tanggal &amp; Jam</th>
                                <th scope="col" class="px-4 py-3">No. Meja</th>
                                <th scope="col" class="px-4 py-3">Kasir</th>
                                <th scope="col" class="px-4 py-3">Detail Pesanan</th>
                                <th scope="col" class="px-4 py-3 text-right">Total Bayar</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line-2">
                            @foreach ($transactions as $transaction)
                                <tr class="align-top transition hover:bg-surface-2">
                                    <th scope="row" class="px-4 py-3 text-left font-bold whitespace-nowrap">
                                        {{ $transaction->invoice_number }}
                                    </th>
                                    <td class="tabular px-4 py-3 whitespace-nowrap">
                                        {{ $transaction->created_at->format('d/m/Y') }}
                                        <span class="block text-xs text-muted">{{ $transaction->created_at->format('H:i:s') }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ $transaction->table_number ? 'Meja '.str_pad($transaction->table_number, 2, '0', STR_PAD_LEFT) : 'Bawa pulang' }}
                                    </td>
                                    <td class="px-4 py-3">{{ $transaction->user?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-ink-2">{{ $transaction->itemSummary() }}</td>
                                    <td class="px-4 py-3 text-right font-bold whitespace-nowrap">
                                        <x-money :value="$transaction->total_amount" />
                                    </td>
                                    <td class="px-4 py-3"><x-status-badge :status="$transaction->payment_status" /></td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end">
                                            <x-button type="button" variant="nav" size="sm"
                                                      x-data
                                                      @click="$dispatch('open-modal', {
                                                          name: 'detail-transaksi',
                                                          payload: { url: '{{ route('manager.transactions.show', $transaction) }}' }
                                                      })">
                                                <x-icon name="search" class="h-4 w-4" />
                                                Detail
                                            </x-button>
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

        <x-card title="Aktivitas Terbaru" padding="p-3" class="h-fit">
            @forelse ($activities as $activity)
                <div class="border-b border-line py-2 text-sm last:border-0">
                    <p class="tabular text-xs font-bold text-nav-700">{{ $activity['time'] }} · {{ $activity['date'] }}</p>
                    <p class="font-semibold text-ink">{{ $activity['user'] }}</p>
                    <p class="text-xs text-muted">{{ $activity['detail'] }}</p>
                </div>
            @empty
                <p class="py-2 text-sm text-muted">Belum ada aktivitas tercatat.</p>
            @endforelse
        </x-card>
    </div>

    {{-- ============ POP-UP DETAIL TRANSAKSI ============ --}}
    <div x-data="transactionDetail()" @open-modal.window="load($event)">
        <x-modal name="detail-transaksi" maxWidth="max-w-3xl">
            <x-slot:heading>
                <span x-text="detail ? 'Detail Transaksi: ' + detail.invoice_number : 'Memuat detail…'">Detail Transaksi</span>
            </x-slot:heading>

            <template x-if="!detail">
                <p class="py-8 text-center text-sm text-muted">Memuat data transaksi…</p>
            </template>

            <template x-if="detail">
                <div class="space-y-4">
                    <dl class="grid gap-3 rounded-xl border border-line bg-surface-2 p-3 sm:grid-cols-4">
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Tanggal &amp; Waktu</dt>
                            <dd class="tabular text-sm font-semibold" x-text="detail.created_at"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">No. Meja</dt>
                            <dd class="text-sm font-semibold" x-text="detail.table"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Kasir</dt>
                            <dd class="text-sm font-semibold" x-text="detail.cashier"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Status</dt>
                            <dd>
                                <span class="inline-flex rounded-md border px-2 py-1 text-xs font-bold uppercase"
                                      :class="detail.is_paid
                                          ? 'border-brand-700/30 bg-brand-100 text-brand-700'
                                          : 'border-danger-700/30 bg-danger-100 text-danger-700'"
                                      x-text="detail.status"></span>
                            </dd>
                        </div>
                    </dl>

                    <div class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
                        <div class="min-w-0">
                            <h3 class="mb-2 text-sm font-bold tracking-tight">Rincian Menu</h3>
                            <div class="overflow-x-auto rounded-lg border border-line scrollbar-slim">
                                <table class="w-full min-w-[380px] text-sm">
                                    <thead>
                                        <tr class="border-b border-line bg-surface-2 text-left text-[11px] font-bold uppercase tracking-wider text-muted">
                                            <th scope="col" class="px-3 py-2">Menu</th>
                                            <th scope="col" class="px-3 py-2 text-center">Qty</th>
                                            <th scope="col" class="px-3 py-2 text-right">Harga Satuan</th>
                                            <th scope="col" class="px-3 py-2 text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line-2">
                                        <template x-for="item in detail.items" :key="item.name">
                                            <tr>
                                                <th scope="row" class="px-3 py-2 text-left font-semibold" x-text="item.name"></th>
                                                <td class="tabular px-3 py-2 text-center" x-text="item.qty"></td>
                                                <td class="tabular px-3 py-2 text-right" x-text="item.price"></td>
                                                <td class="tabular px-3 py-2 text-right font-bold" x-text="item.subtotal"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-t-2 border-line bg-bg">
                                            <th scope="row" colspan="3" class="px-3 py-2 text-right text-sm font-extrabold uppercase">
                                                Total Pesanan
                                            </th>
                                            <td class="tabular px-3 py-2 text-right font-extrabold" x-text="detail.total"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="rounded-lg border border-nav-600/30 bg-nav-100 p-3 text-center">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-nav-700">Tanggal Proses</p>
                                <p class="tabular text-sm font-semibold text-nav-800" x-text="detail.paid_at"></p>
                                <hr class="my-2 border-nav-600/20">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-nav-700">Total Bayar</p>
                                <p class="tabular text-2xl font-extrabold text-nav-800" x-text="detail.total"></p>
                                <template x-if="detail.is_paid">
                                    <div class="mt-2 space-y-0.5 text-left text-xs">
                                        <p class="flex justify-between">
                                            <span class="text-nav-700">Uang dibayar</span>
                                            <span class="tabular font-bold" x-text="detail.pay_amount"></span>
                                        </p>
                                        <p class="flex justify-between">
                                            <span class="text-nav-700">Kembalian</span>
                                            <span class="tabular font-bold" x-text="detail.change_amount"></span>
                                        </p>
                                    </div>
                                </template>
                            </div>

                            <template x-if="detail.is_paid">
                                <a :href="detail.receipt_url" target="_blank"
                                   class="flex min-h-12 w-full items-center justify-center gap-2 rounded-lg bg-nav-600 px-4 font-semibold text-white transition hover:bg-nav-700">
                                    <x-icon name="printer" class="h-5 w-5" />
                                    Cetak Struk
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </x-modal>
    </div>

    <script>
        function transactionDetail() {
            return {
                detail: null,

                async load(event) {
                    if (event.detail?.name !== 'detail-transaksi') return;

                    this.detail = null;

                    try {
                        const response = await fetch(event.detail.payload.url, {
                            headers: { 'Accept': 'application/json' },
                        });

                        if (!response.ok) throw new Error(response.statusText);

                        this.detail = await response.json();
                    } catch (error) {
                        console.error('Gagal memuat detail transaksi:', error);
                    }
                },
            };
        }
    </script>

</x-layouts.app>
