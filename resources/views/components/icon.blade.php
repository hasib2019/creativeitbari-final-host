@props(['name' => null, 'fallback' => 'heroicon-o-sparkles'])
@php
    $class = $attributes->get('class', 'size-6');
    $extra = array_merge(['aria-hidden' => 'true'], $attributes->except('class')->getAttributes());
    try {
        $svg = svg($name ?: $fallback, $class, $extra)->toHtml();
    } catch (\Throwable $e) {
        $svg = svg($fallback, $class, $extra)->toHtml();
    }
@endphp
{!! $svg !!}
