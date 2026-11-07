@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-sm hover:bg-brand-700 active:bg-brand-800 focus-visible:outline-brand-700',
        'nav' => 'bg-nav-600 text-white shadow-sm hover:bg-nav-700 active:bg-nav-800 focus-visible:outline-nav-800',
        'danger' => 'bg-danger-600 text-white shadow-sm hover:bg-danger-700 focus-visible:outline-danger-700',
        'secondary' => 'border border-line bg-surface text-ink-2 shadow-sm hover:bg-surface-2 hover:border-line active:bg-bg focus-visible:outline-nav-600',
        'soft' => 'bg-nav-100 text-nav-700 hover:bg-nav-200 focus-visible:outline-nav-600',
        'soft-brand' => 'bg-brand-100 text-brand-700 hover:bg-brand-200 focus-visible:outline-brand-600',
        'outline-danger' => 'border border-danger-600/30 bg-surface text-danger-700 hover:bg-danger-100 focus-visible:outline-danger-700',
        'ghost' => 'text-ink-2 hover:bg-bg focus-visible:outline-nav-600',
    ];

    $sizes = [
        'sm' => 'min-h-9 px-3 py-1.5 text-xs gap-1.5 rounded-lg',
        'md' => 'min-h-11 px-4 py-2 text-sm gap-2 rounded-xl',
        'lg' => 'min-h-12 px-5 py-2.5 text-sm gap-2 rounded-xl',
    ];

    $classes = implode(' ', [
        'inline-flex items-center justify-center font-semibold transition-colors duration-150',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline',
        'disabled:cursor-not-allowed disabled:opacity-45 disabled:shadow-none',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
