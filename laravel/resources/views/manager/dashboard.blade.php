<x-layouts.app title="Dashboard" heading="Dashboard Manajer">

    {{-- ============ KARTU RINGKASAN ============ --}}
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">

        <x-stat label="Transaksi Hari Ini"
                :value="number_format($summary['today_count'], 0, ',', '.')"
                :sub="\App\Support\Money::format($summary['today_revenue'])"
                icon="document" tone="nav" />

        <x-stat label="Transaksi {{ $summary['month_label'] }}"
                :value="number_format($summary['month_count'], 0, ',', '.')"
                :sub="\App\Support\Money::format($summary['month_revenue'])"
                icon="cash" tone="brand" />

        <x-stat label="Transaksi Belum Bayar"
                :value="number_format($summary['pending_count'], 0, ',', '.')"
                :sub="$summary['pending_count'] > 0
                    ? \App\Support\Money::format($summary['pending_value']).' belum lunas'
                    : 'Semua transaksi sudah lunas'"
                icon="warning"
                :tone="$summary['pending_count'] > 0 ? 'danger' : 'brand'"
                :href="route('cashier.transactions.index', ['status' => 'belum_bayar'])" />

        <x-stat label="Menu Tersedia"
                :value="number_format($summary['menu_available'], 0, ',', '.')"
                :sub="$summary['menu_sold_out'] > 0
                    ? $summary['menu_sold_out'].' menu berstatus Habis'
                    : 'Tidak ada menu yang habis'"
                icon="list"
                :tone="$summary['menu_sold_out'] > 0 ? 'warn' : 'nav'"
                :href="route('manager.menus.index')" />
    </div>

    {{-- ============ GRAFIK & AKTIVITAS ============ --}}
    <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)]">

        <x-card title="Grafik Transaksi & Omzet" subtitle="8 hari terakhir" class="min-w-0 xl:self-start">
            <div class="relative h-72">
                <canvas id="grafikHarian"
                        role="img"
                        aria-label="Grafik batang jumlah transaksi dan garis omzet selama 8 hari terakhir"></canvas>
            </div>

            {{-- Tabel setara untuk pembaca layar & bila grafik tidak tampil --}}
            <details class="mt-3 text-sm">
                <summary class="cursor-pointer font-semibold text-nav-700">Lihat angka grafik dalam bentuk tabel</summary>
                <table class="mt-2 w-full text-sm">
                    <thead>
                        <tr class="border-b border-line text-left text-xs font-semibold text-ink-2">
                            <th scope="col" class="py-1.5">Tanggal</th>
                            <th scope="col" class="py-1.5 text-right">Transaksi</th>
                            <th scope="col" class="py-1.5 text-right">Omzet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-2">
                        @foreach ($chart['labels'] as $i => $label)
                            <tr>
                                <th scope="row" class="py-1.5 text-left font-medium">{{ $label }}</th>
                                <td class="tabular py-1.5 text-right">{{ $chart['counts'][$i] }}</td>
                                <td class="tabular py-1.5 text-right"><x-money :value="$chart['revenues'][$i]" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        </x-card>

        <div class="min-w-0 space-y-4">

            <x-card title="Menu Terlaris" subtitle="5 teratas, 30 hari terakhir">
                @if (empty($bestSellers))
                    <x-empty title="Belum ada penjualan" message="Grafik menu terlaris muncul setelah ada transaksi lunas." />
                @else
                    @php $maxQty = max(array_column($bestSellers, 'qty')) ?: 1; @endphp
                    <ol class="space-y-2.5">
                        @foreach ($bestSellers as $index => $menu)
                            <li>
                                <div class="mb-1 flex items-baseline justify-between gap-2 text-sm">
                                    <span class="truncate text-sm font-semibold text-ink">
                                        {{ $index + 1 }}. {{ $menu['name'] }}
                                    </span>
                                    <span class="tabular shrink-0 font-extrabold text-brand-600">
                                        <x-money :value="$menu['revenue']" />
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-line-2">
                                        <div class="h-full rounded-full bg-nav-600"
                                             style="width: {{ round($menu['qty'] / $maxQty * 100) }}%"></div>
                                    </div>
                                    <span class="tabular w-20 shrink-0 text-right text-xs font-semibold text-muted">
                                        {{ $menu['qty'] }} porsi
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-card>

            <x-card title="Aktivitas Terbaru" padding="p-0">
                @if (empty($activities))
                    <x-empty title="Belum ada aktivitas" />
                @else
                    <div class="overflow-x-auto scrollbar-slim">
                        <table class="w-full min-w-[460px] text-sm">
                            <thead>
                                <tr class="border-b border-line bg-surface-2 text-left text-[11px] font-bold uppercase tracking-wider text-muted">
                                    <th scope="col" class="px-3 py-2">Waktu</th>
                                    <th scope="col" class="px-3 py-2">Kategori</th>
                                    <th scope="col" class="px-3 py-2">Pengguna</th>
                                    <th scope="col" class="px-3 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line-2">
                                @foreach ($activities as $activity)
                                    <tr>
                                        <th scope="row" class="tabular px-3 py-2 text-left font-semibold whitespace-nowrap">
                                            {{ $activity['time'] }}
                                            <span class="block text-xs font-normal text-muted">{{ $activity['date'] }}</span>
                                        </th>
                                        <td class="px-3 py-2">
                                            {{ $activity['category'] }}
                                            <span class="block text-xs text-muted">{{ $activity['detail'] }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-ink-2">{{ $activity['user'] }}</td>
                                        <td class="px-3 py-2">
                                            <span @class([
                                                'inline-flex rounded border px-1.5 py-0.5 text-xs font-bold uppercase',
                                                'border-warn-700/30 bg-warn-100 text-warn-700' => $activity['status'] === 'Pending',
                                                'border-danger-700/30 bg-danger-100 text-danger-700' => $activity['status'] === 'Dihapus',
                                                'border-brand-700/20 bg-brand-100 text-brand-700' => ! in_array($activity['status'], ['Pending', 'Dihapus'], true),
                                            ])>{{ $activity['status'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            const canvas = document.getElementById('grafikHarian');
            if (!canvas) return;

            new window.Chart(canvas, {
                data: {
                    labels: @json($chart['labels']),
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Jumlah transaksi',
                            data: @json($chart['counts']),
                            backgroundColor: '#2A64B0',
                            borderRadius: 4,
                            yAxisID: 'y',
                            order: 2,
                        },
                        {
                            type: 'line',
                            label: 'Omzet (Rp)',
                            data: @json($chart['revenues']),
                            borderColor: '#1A7A3E',
                            backgroundColor: '#1A7A3E',
                            borderWidth: 2,
                            tension: 0.3,
                            pointRadius: 3,
                            yAxisID: 'y1',
                            order: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 14, usePointStyle: true } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ctx.dataset.yAxisID === 'y1'
                                    ? `Omzet: ${window.rupiah(ctx.parsed.y)}`
                                    : `Transaksi: ${ctx.parsed.y}`,
                            },
                        },
                    },
                    scales: {
                        y: {
                            position: 'left',
                            beginAtZero: true,
                            title: { display: true, text: 'Jumlah transaksi' },
                            grid: { color: '#EDF1F4' },
                        },
                        y1: {
                            position: 'right',
                            beginAtZero: true,
                            // Judul sumbu dihilangkan: di layar sempit teksnya menimpa label angka.
                            grid: { drawOnChartArea: false },
                            ticks: {
                                callback: (value) => value >= 1000000
                                    ? 'Rp' + (value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + 'jt'
                                    : 'Rp' + (value / 1000).toLocaleString('id-ID') + 'rb',
                            },
                        },
                        x: { grid: { display: false } },
                    },
                },
            });
        });
    </script>

</x-layouts.app>
