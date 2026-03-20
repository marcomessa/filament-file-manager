@props([
    'item',
    'isFolder' => false,
    'size' => 'sm',
])

@php
    $buttonClass = $size === 'sm'
        ? 'flex size-6 items-center justify-center rounded-md'
        : 'flex size-7 items-center justify-center rounded-md';
    $iconClass = $size === 'sm' ? 'size-3.5' : 'size-4';
@endphp

@if (! $isFolder)
    <button
        wire:click="mountAction('preview', { path: @js($item->path) })"
        type="button"
        class="{{ $buttonClass }} text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
        title="{{ __('filament-file-manager::file-manager.actions.preview') }}"
    >
        <x-filament::icon icon="heroicon-m-eye" :class="$iconClass" />
    </button>
    <button
        wire:click="downloadFile(@js($item->path))"
        type="button"
        class="{{ $buttonClass }} text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
        title="{{ __('filament-file-manager::file-manager.actions.download') }}"
    >
        <x-filament::icon icon="heroicon-m-arrow-down-tray" :class="$iconClass" />
    </button>
@endif
<button
    wire:click="mountAction('rename', { path: @js($item->path) })"
    type="button"
    class="{{ $buttonClass }} text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
    title="{{ __('filament-file-manager::file-manager.actions.rename') }}"
>
    <x-filament::icon icon="heroicon-m-pencil" :class="$iconClass" />
</button>
<button
    wire:click="mountAction('deleteItem', { path: @js($item->path) })"
    type="button"
    class="{{ $buttonClass }} text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10"
    title="{{ __('filament-file-manager::file-manager.actions.delete') }}"
>
    <x-filament::icon icon="heroicon-m-trash" :class="$iconClass" />
</button>
