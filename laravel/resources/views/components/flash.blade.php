@php
    $success = session('success');
    $error = session('error') ?? ($errors->has('payment_status') ? $errors->first('payment_status') : null);
    $status = session('status');
@endphp

@if ($success || $error || $status)
    <div class="mb-5 space-y-2.5" aria-live="polite">
        @if ($success)
            <div x-data="{ show: true }" x-show="show" x-cloak x-transition
                 class="flex items-start gap-2.5 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-700 shadow-card">
                <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0" />
                <p class="flex-1">{{ $success }}</p>
                <button type="button" @click="show = false" class="shrink-0 rounded-md p-0.5 transition hover:bg-brand-600/10" aria-label="Tutup pesan">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>
        @endif

        @if ($error)
            <div x-data="{ show: true }" x-show="show" x-cloak x-transition
                 class="flex items-start gap-2.5 rounded-xl border border-danger-600/25 bg-danger-100 px-4 py-3 text-sm font-medium text-danger-700 shadow-card">
                <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0" />
                <p class="flex-1">{{ $error }}</p>
                <button type="button" @click="show = false" class="shrink-0 rounded-md p-0.5 transition hover:bg-danger-600/10" aria-label="Tutup pesan">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>
        @endif

        @if ($status)
            <div class="rounded-xl border border-nav-200 bg-nav-50 px-4 py-3 text-sm font-medium text-nav-700 shadow-card">
                {{ $status }}
            </div>
        @endif
    </div>
@endif
