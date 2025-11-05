@php
    $target = $url ?? url()->previous();
@endphp

<a href="{{ $target }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center text-blue-600 hover:underline mb-4']) }}>
    ← {{ $slot ?: 'Back' }}
</a>
