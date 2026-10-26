@if ($paginator->hasPages())
    @php
        $base = 'inline-flex min-h-11 items-center gap-1.5 rounded-xl border px-3 text-sm font-semibold transition';
        $linkClass = $base.' border-line bg-surface text-ink-2 hover:bg-surface-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-nav-600';
        $disabledClass = $base.' cursor-not-allowed border-line bg-bg text-muted/60';

        // simplePaginate() memakai Paginator biasa yang tidak punya total()/lastPage().
        $hasTotals = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    @endphp

    {{--
        Daftar nomor halaman bawaan Laravel melebar keluar layar saat jumlah
        halaman banyak. Di sini cukup Sebelumnya / Berikutnya + keterangan posisi,
        sehingga tetap muat sampai lebar 320px.
    --}}
    <nav role="navigation" aria-label="Navigasi halaman"
         class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2">

        <p class="tabular min-w-0 text-xs text-muted">
            Menampilkan
            <span class="font-semibold text-ink-2">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-ink-2">{{ $paginator->lastItem() }}</span>
            @if ($hasTotals)
                dari <span class="font-semibold text-ink-2">{{ $paginator->total() }}</span>
                <span class="whitespace-nowrap">· Hal. {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}</span>
            @else
                <span class="whitespace-nowrap">· Hal. {{ $paginator->currentPage() }}</span>
            @endif
        </p>

        <div class="flex flex-wrap items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="{{ $disabledClass }}" aria-disabled="true">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $linkClass }}">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $linkClass }}">
                    Berikutnya
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </a>
            @else
                <span class="{{ $disabledClass }}" aria-disabled="true">
                    Berikutnya
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
