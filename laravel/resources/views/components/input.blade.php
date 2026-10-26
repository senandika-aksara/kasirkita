@props(['label' => null, 'name', 'type' => 'text', 'hint' => null])

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $name }}" class="block text-xs font-semibold text-ink-2">{{ $label }}</label>
    @endif

    <input type="{{ $type }}"
           id="{{ $name }}"
           name="{{ $name }}"
           @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
           {{ $attributes->merge([
               'class' => 'block min-h-11 w-full rounded-xl border px-3.5 py-2 text-sm text-ink transition placeholder:text-muted/60 focus:outline-2 focus:outline-offset-0 '
                   . ($errors->has($name)
                       ? 'border-danger-600 bg-danger-100/40 focus:outline-danger-600'
                       : 'border-line bg-surface hover:border-line focus:border-nav-500 focus:outline-nav-500/40'),
           ]) }}>

    @if ($hint)
        <p class="text-xs text-muted">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $name }}-error" class="text-xs font-medium text-danger-700">{{ $message }}</p>
    @enderror
</div>
