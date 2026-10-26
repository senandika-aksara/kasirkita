@props(['status'])

{{-- Status selalu ditampilkan sebagai teks + warna, tidak hanya warna. --}}
<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide '
        . $status->badgeClass(),
]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-70" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
