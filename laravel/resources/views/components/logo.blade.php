@props(['class' => 'h-8 w-8'])

{{-- Lambang KasirKita: huruf K dari dua bidang hijau & biru --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 48 48" fill="none" role="img" aria-label="Logo KasirKita">
    <rect width="48" height="48" rx="10" fill="#1A7A3E" />
    <path d="M14 10h7v11.5L31.5 10H40L28 23.5 40 38h-8.8l-8.4-10.4-1.8 2V38h-7V10z" fill="#FFFFFF" />
    <path d="M31.5 10H40L28 23.5l-3.8-4.4L31.5 10z" fill="#2A64B0" />
</svg>
