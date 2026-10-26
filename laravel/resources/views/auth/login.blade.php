<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Sistem — KasirKita</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-bg antialiased">

<div class="grid min-h-screen lg:grid-cols-2">

    {{-- Panel kiri: identitas aplikasi. Disembunyikan di layar kecil. --}}
    <aside class="relative hidden overflow-hidden bg-nav-900 p-12 lg:flex lg:flex-col lg:justify-between">
        {{-- Ornamen cahaya lembut, bukan warna blok datar --}}
        <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-brand-500/18 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-nav-500/18 blur-3xl" aria-hidden="true"></div>

        <div class="relative flex items-center gap-3">
            <x-logo class="h-11 w-11" />
            <span class="leading-tight">
                <span class="block text-xl font-extrabold tracking-tight text-white">
                    Kasir<span class="text-brand-500">Kita</span>
                </span>
                <span class="block text-xs text-white/50">Aplikasi Kasir Sederhana</span>
            </span>
        </div>

        <div class="relative max-w-md">
            <h2 class="text-3xl font-extrabold leading-tight tracking-tight text-white">
                Catat pesanan, tandai lunas, lihat rekapnya.
            </h2>
            <p class="mt-4 text-sm leading-relaxed text-white/60">
                Pencatatan transaksi penjualan untuk UMKM kuliner — total dihitung otomatis,
                kembalian langsung muncul, dan rekap penjualan tersaji rapi di dashboard.
            </p>

            <ul class="mt-8 space-y-3">
                @foreach ([
                    'Total & kembalian dihitung otomatis',
                    'Riwayat transaksi tersimpan rapi',
                    'Rekap penjualan harian & bulanan',
                ] as $poin)
                    <li class="flex items-center gap-3 text-sm text-white/75">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-500/20 text-brand-500">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </span>
                        {{ $poin }}
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-white/35">
            Capstone Project STSI4440 · Universitas Terbuka
        </p>
    </aside>

    {{-- Panel kanan: formulir masuk --}}
    <main class="flex items-center justify-center p-6 sm:p-10">
        <div class="w-full max-w-sm">

            {{-- Merek versi ringkas untuk layar kecil --}}
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <x-logo class="h-10 w-10" />
                <span class="leading-tight">
                    <span class="block text-lg font-extrabold tracking-tight text-ink">
                        Kasir<span class="text-brand-600">Kita</span>
                    </span>
                    <span class="block text-xs text-muted">Aplikasi Kasir Sederhana</span>
                </span>
            </div>

            <h1 class="text-2xl font-extrabold tracking-tight text-ink">Masuk ke sistem</h1>
            <p class="mt-1.5 text-sm text-muted">Gunakan username dan password yang diberikan manajer.</p>

            @if (session('status'))
                <div class="mt-5 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-danger-600/25 bg-danger-100 px-4 py-3 text-sm font-medium text-danger-700">
                    <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0" />
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4"
                  x-data="{ showPassword: false, sending: false }" @submit="sending = true">
                @csrf

                <div class="space-y-1.5">
                    <label for="username" class="block text-xs font-semibold text-ink-2">Username</label>
                    <input id="username" name="username" type="text"
                           value="{{ old('username') }}"
                           placeholder="Masukkan username"
                           autocomplete="username" required autofocus
                           @class([
                               'block min-h-11 w-full rounded-xl border bg-surface px-3.5 py-2 text-sm text-ink transition placeholder:text-muted/60 focus:outline-2 focus:outline-offset-0',
                               'border-danger-600 bg-danger-100/40 focus:outline-danger-600' => $errors->has('username'),
                               'border-line focus:border-nav-500 focus:outline-nav-500/40' => ! $errors->has('username'),
                           ])>
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-semibold text-ink-2">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'"
                               id="password" name="password"
                               placeholder="Masukkan password"
                               autocomplete="current-password" required
                               @class([
                                   'block min-h-11 w-full rounded-xl border bg-surface px-3.5 py-2 pr-12 text-sm text-ink transition placeholder:text-muted/60 focus:outline-2 focus:outline-offset-0',
                                   'border-danger-600 bg-danger-100/40 focus:outline-danger-600' => $errors->has('password'),
                                   'border-line focus:border-nav-500 focus:outline-nav-500/40' => ! $errors->has('password'),
                               ])>
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-xl text-muted transition hover:text-ink-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-nav-500"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                            <span x-show="!showPassword"><x-icon name="eye" class="h-5 w-5" /></span>
                            <span x-cloak x-show="showPassword"><x-icon name="eye-off" class="h-5 w-5" /></span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-2">
                        <input type="checkbox" name="remember" value="1"
                               class="h-4 w-4 rounded border-line text-brand-600 focus:ring-brand-600">
                        Ingat saya
                    </label>
                    <span class="text-xs text-muted">Lupa password? Hubungi manajer.</span>
                </div>

                <x-button type="submit" variant="primary" size="lg" class="w-full" x-bind:disabled="sending">
                    <x-icon name="login" class="h-5 w-5" />
                    <span x-text="sending ? 'Memproses…' : 'Masuk Sistem'">Masuk Sistem</span>
                </x-button>
            </form>

        </div>
    </main>
</div>

</body>
</html>
