@php $user = auth()->user(); @endphp

<div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative shrink-0">
    <button type="button"
            @click="open = !open"
            :aria-expanded="open"
            aria-haspopup="menu"
            class="flex min-h-11 items-center gap-2 rounded-xl border border-line bg-surface py-1 pl-1 pr-2.5 transition hover:bg-surface-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-nav-600">
        <x-avatar :user="$user" class="h-8 w-8" />
        <span class="hidden text-left leading-tight sm:block">
            <span class="block text-xs font-semibold text-ink">{{ $user->name }}</span>
            <span class="block text-[10px] text-muted">{{ $user->role->label() }}</span>
        </span>
        <svg class="h-4 w-4 text-muted transition" :class="open && 'rotate-180'"
             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-cloak x-show="open" @click.outside="open = false" role="menu"
         x-transition.origin.top.right
         class="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-line bg-surface shadow-pop">
        <div class="flex items-center gap-3 border-b border-line-2 px-4 py-3.5">
            <x-avatar :user="$user" class="h-10 w-10" />
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-ink">{{ $user->name }}</p>
                <p class="truncate text-xs text-muted">{{ '@' . $user->username }}</p>
            </div>
        </div>
        <div class="px-4 py-2.5">
            <span class="inline-flex rounded-lg border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide {{ $user->role->badgeClass() }}">
                {{ $user->role->label() }}
            </span>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="border-t border-line-2">
            @csrf
            <button type="submit" role="menuitem"
                    class="flex min-h-11 w-full items-center gap-2.5 px-4 py-3 text-left text-sm font-semibold text-danger-700 transition hover:bg-danger-100">
                <x-icon name="logout" class="h-5 w-5" />
                Keluar dari sistem
            </button>
        </form>
    </div>
</div>
