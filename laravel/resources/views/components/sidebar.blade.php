@php
    $user = auth()->user();

    $items = [];

    if ($user->isManagerial()) {
        $items[] = ['label' => 'Dashboard', 'route' => 'manager.dashboard', 'active' => 'manager.dashboard', 'icon' => 'dashboard'];
        $items[] = ['label' => 'Transaksi', 'route' => 'manager.transactions.index', 'active' => 'manager.transactions.*', 'icon' => 'document'];
        $items[] = ['label' => 'Menu', 'route' => 'manager.menus.index', 'active' => 'manager.menus.*', 'icon' => 'list'];
        $items[] = ['label' => 'Pengguna', 'route' => 'manager.users.index', 'active' => 'manager.users.*', 'icon' => 'users'];
        $items[] = ['label' => 'Kasir', 'route' => 'cashier.transactions.index', 'active' => 'cashier.*', 'icon' => 'cash'];
    } else {
        $items[] = ['label' => 'Transaksi', 'route' => 'cashier.transactions.index', 'active' => 'cashier.transactions.*', 'icon' => 'document'];
        $items[] = ['label' => 'Kasir Baru', 'route' => 'cashier.pos.create', 'active' => 'cashier.pos.*', 'icon' => 'cash'];
    }
@endphp

<nav x-data="{ open: false }"
     @toggle-sidebar.window="open = !open"
     @keydown.escape.window="open = false"
     class="shrink-0"
     aria-label="Navigasi utama">

    {{-- Latar gelap saat sidebar dibuka di layar kecil --}}
    <div x-cloak
         x-show="open"
         x-transition.opacity
         @click="open = false"
         class="fixed inset-0 z-30 bg-ink/50 lg:hidden"
         aria-hidden="true"></div>

    {{--
        Panel sidebar selalu position:fixed supaya latarnya setinggi layar penuh dan
        tidak ikut tergulir. Di layar lebar, pengatur jarak di bawah ini yang
        menyediakan ruang selebar panel agar isi halaman tidak tertimpa.
    --}}
    <div class="hidden w-60 shrink-0 lg:block" aria-hidden="true"></div>

    <div :class="open ? 'translate-x-0' : '-translate-x-full'"
         class="fixed inset-y-0 left-0 z-40 flex w-60 flex-col bg-nav-900 transition-transform duration-200 lg:translate-x-0">

        {{-- Merek --}}
        <div class="flex h-16 shrink-0 items-center gap-2.5 px-5">
            <x-logo class="h-9 w-9 shrink-0" />
            <span class="min-w-0 leading-tight">
                <span class="block text-base font-extrabold tracking-tight text-white">
                    Kasir<span class="text-brand-500">Kita</span>
                </span>
                <span class="block text-[10px] font-medium tracking-wide text-white/45">
                    Aplikasi Kasir Sederhana
                </span>
            </span>
        </div>

        <ul class="flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto px-3 py-3 scrollbar-slim">
            @foreach ($items as $item)
                @php $isActive = request()->routeIs($item['active']); @endphp
                <li>
                    <a href="{{ route($item['route']) }}"
                       @click="open = false"
                       @class([
                           'flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
                           'bg-white/12 text-white shadow-[inset_2px_0_0_0_var(--color-brand-500)]' => $isActive,
                           'text-white/60 hover:bg-white/8 hover:text-white' => ! $isActive,
                       ])
                       @if ($isActive) aria-current="page" @endif>
                        <x-icon :name="$item['icon']"
                                class="h-5 w-5 shrink-0 {{ $isActive ? 'text-brand-500' : '' }}" />
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Identitas pengguna di kaki sidebar --}}
        <div class="shrink-0 border-t border-white/10 px-3 py-3">
            <div class="flex items-center gap-2.5 rounded-xl px-2 py-1.5">
                <x-avatar :user="$user" class="h-9 w-9 ring-1 ring-white/15" />
                <span class="min-w-0 flex-1 leading-tight">
                    <span class="block truncate text-sm font-semibold text-white">{{ $user->name }}</span>
                    <span class="block truncate text-[11px] text-white/45">{{ $user->role->label() }}</span>
                </span>
            </div>
        </div>
    </div>
</nav>
