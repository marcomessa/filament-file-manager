@props([
    'item',
    'size' => 'sm',
])

@php
    $iconClass = match ($size) {
        'lg' => 'size-10',
        default => 'size-5',
    };
    $imgClass = match ($size) {
        'lg' => 'size-full object-cover',
        default => 'size-8 rounded object-cover',
    };
@endphp

@if ($item->hasThumbnail())
    <img
        src="{{ $item->thumbnailUrl }}"
        alt="{{ $item->name }}"
        class="{{ $imgClass }}"
        loading="lazy"
    />
@else
    <x-filament::icon :icon="$item->category->icon()" @class([$iconClass, $item->category->color()]) />
@endif
