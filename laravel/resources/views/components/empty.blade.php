@props(['title' => 'Belum ada data', 'message' => null, 'icon' => 'inbox'])

<div class="flex flex-col items-center justify-center gap-2.5 px-4 py-14 text-center">
    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-bg text-muted/70">
        <x-icon :name="$icon" class="h-7 w-7" />
    </span>
    <p class="text-sm font-semibold text-ink">{{ $title }}</p>
    @if ($message)
        <p class="max-w-sm text-sm text-muted">{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
