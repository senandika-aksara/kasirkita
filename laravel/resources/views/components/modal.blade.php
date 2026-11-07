@props([
    'name',
    'title' => '',
    'maxWidth' => 'max-w-2xl',
    'headerClass' => 'text-ink',
])

{{--
    Modal Alpine: dibuka lewat $dispatch('open-modal', { name: '...', payload: {...} }).
    Payload tersedia di dalam slot sebagai `data`.
--}}
<div x-data="{
         open: false,
         data: {},
         show(event) {
             if (event.detail?.name !== '{{ $name }}') return;
             this.data = event.detail.payload ?? {};
             this.open = true;
             $nextTick(() => $refs.panel?.querySelector('[autofocus]')?.focus());
         },
         close() { this.open = false; },
     }"
     @open-modal.window="show($event)"
     @close-modal.window="close()"
     @keydown.escape.window="close()"
     x-cloak
     x-show="open"
     class="fixed inset-0 z-50 overflow-y-auto"
     role="dialog"
     aria-modal="true"
     aria-labelledby="{{ $name }}-title">

    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-ink/45 backdrop-blur-[2px]" @click="close()" aria-hidden="true"></div>

    <div class="relative flex min-h-full items-start justify-center p-4 sm:items-center">
        <div x-ref="panel"
             x-show="open"
             x-transition
             @click.outside="close()"
             class="w-full {{ $maxWidth }} overflow-hidden rounded-2xl border border-line bg-surface shadow-pop">

            <header class="flex items-center justify-between gap-3 border-b border-line-2 px-5 py-4 {{ $headerClass }}">
                <h2 id="{{ $name }}-title" class="text-base font-bold tracking-tight sm:text-lg">
                    @isset($heading)
                        {{ $heading }}
                    @else
                        {{ $title }}
                    @endisset
                </h2>
                <button type="button" @click="close()"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-muted transition hover:bg-bg hover:text-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-nav-600"
                        aria-label="Tutup">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </header>

            <div class="max-h-[70vh] overflow-y-auto p-5 scrollbar-slim">
                {{ $slot }}
            </div>

            @isset($footer)
                <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-line-2 bg-surface-2 px-5 py-3.5">
                    {{ $footer }}
                </footer>
            @endisset
        </div>
    </div>
</div>
