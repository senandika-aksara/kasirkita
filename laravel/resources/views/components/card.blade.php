@props(['title' => null, 'subtitle' => null, 'actions' => null, 'padding' => 'p-5'])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-line bg-surface shadow-card']) }}>
    @if ($title || $actions)
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-2 px-5 py-4">
            @if ($title)
                <div class="min-w-0">
                    <h2 class="text-base font-bold tracking-tight text-ink">{{ $title }}</h2>
                    @if ($subtitle)
                        <p class="mt-0.5 text-xs text-muted">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif
            @if ($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endif
        </header>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</section>
