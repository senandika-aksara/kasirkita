@props(['value' => 0])

<span {{ $attributes->merge(['class' => 'tabular']) }}>{{ \App\Support\Money::format((int) $value) }}</span>
