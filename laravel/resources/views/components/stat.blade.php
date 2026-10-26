@props([
    'label',
    'value',
    'sub' => null,
    'icon' => 'document',
    'tone' => 'nav',
    'href' => null,
])

@php
    $tones = [
        'nav' => ['text-nav-700', 'bg-nav-100'],
        'brand' => ['text-brand-600', 'bg-brand-100'],
        'danger' => ['text-danger-700', 'bg-danger-100'],
        'warn' => ['text-warn-700', 'bg-warn-100'],
    ];

    [$textTone, $bgTone] = $tones[$tone] ?? $tones['nav'];

    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge([
        'class' => 'group flex items-start gap-3.5 rounded-2xl border border-line bg-surface p-5 shadow-card transition'
            . ($href ? ' hover:-translate-y-0.5 hover:shadow-raised focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-nav-600' : ''),
    ]) }}>

    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $bgTone }} {{ $textTone }}">
        <x-icon :name="$icon" class="h-5 w-5" />
    </span>

    <span class="min-w-0 flex-1">
        <span class="block text-[11px] font-bold uppercase leading-tight tracking-wider text-muted">{{ $label }}</span>
        <span class="tabular mt-1 block text-[26px] font-extrabold leading-none tracking-tight text-ink">{{ $value }}</span>
        @if ($sub)
            <span class="tabular mt-1.5 block truncate text-xs font-medium {{ $textTone }}">{{ $sub }}</span>
        @endif
    </span>
</{{ $tag }}>
