@props(['user', 'class' => 'h-9 w-9'])

@if ($user->photo)
    <img src="{{ $user->photoUrl() }}"
         alt="Foto {{ $user->name }}"
         {{ $attributes->merge(['class' => $class . ' shrink-0 rounded-full border border-line object-cover']) }}>
@else
    <span {{ $attributes->merge([
        'class' => $class . ' flex shrink-0 items-center justify-center rounded-full bg-nav-100 text-xs font-bold text-nav-700',
    ]) }} aria-hidden="true">{{ $user->initials() }}</span>
@endif
