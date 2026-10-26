<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'KasirKita' }} — KasirKita</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-bg text-ink antialiased">

<div class="flex min-h-screen">

    <x-sidebar />

    <div class="flex min-w-0 flex-1 flex-col">

        {{-- Header satu baris: judul layar kiri, tanggal & akun kanan --}}
        <header class="sticky top-0 z-20 border-b border-line bg-surface/85 backdrop-blur-sm">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6">

                <button type="button"
                        class="-ml-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-ink-2 hover:bg-bg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-nav-600 lg:hidden"
                        aria-label="Buka menu navigasi"
                        x-data
                        @click="$dispatch('toggle-sidebar')">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-lg font-bold tracking-tight text-ink sm:text-xl">
                        {{ $heading ?? ($title ?? 'KasirKita') }}
                    </h1>
                    <p class="truncate text-xs text-muted">{{ $subheading ?? \App\Support\Clock::dateLong(now()) }}</p>
                </div>

                {{-- Jam berjalan; disembunyikan di layar sempit agar judul tetap lega --}}
                <p class="hidden shrink-0 items-center gap-1.5 rounded-lg border border-line bg-surface-2 px-3 py-1.5 text-xs font-semibold text-ink-2 md:flex">
                    <x-icon name="clock" class="h-4 w-4 text-muted" />
                    <span class="tabular">{{ now()->format('H:i') }} WIB</span>
                </p>

                <x-user-menu />
            </div>
        </header>

        <main class="min-w-0 flex-1 p-4 sm:p-6">
            <x-flash />
            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
