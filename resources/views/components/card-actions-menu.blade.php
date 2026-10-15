@props([
    'item',
    'isFolder' => false,
    'permissions' => [],
])

{{-- Grid cards are too narrow for an inline toolbar, so actions live in a compact "more" menu --}}
<x-filament::dropdown placement="bottom-end" teleport>
    <x-slot name="trigger">
        <button
            type="button"
            class="flex size-6 items-center justify-center rounded-md bg-white/90 text-gray-500 shadow-sm ring-1 ring-gray-950/5 backdrop-blur-sm transition hover:text-gray-700 dark:bg-gray-800/90 dark:text-gray-400 dark:ring-white/10 dark:hover:text-gray-200"
            title="{{ __('filament-file-manager::file-manager.actions.more_actions') }}"
            @click.stop
        >
            <x-filament::icon icon="heroicon-m-ellipsis-horizontal" class="size-4" />
        </button>
    </x-slot>

    <x-filament::dropdown.list>
        @if (! $isFolder)
            <x-filament::dropdown.list.item
                x-on:click="$el.closest('.fi-dropdown-panel')?.close()"
                wire:click="mountAction('preview', { path: {{ \Illuminate\Support\Js::from($item->path) }} })"
                icon="heroicon-m-eye"
            >
                {{ __('filament-file-manager::file-manager.actions.preview') }}
            </x-filament::dropdown.list.item>
            @if ($permissions['canDownload'] ?? true)
                <x-filament::dropdown.list.item
                    x-on:click="$el.closest('.fi-dropdown-panel')?.close()"
                    wire:click="downloadFile({{ \Illuminate\Support\Js::from($item->path) }})"
                    icon="heroicon-m-arrow-down-tray"
                >
                    {{ __('filament-file-manager::file-manager.actions.download') }}
                </x-filament::dropdown.list.item>
            @endif
        @endif
        @if ($permissions['canRename'] ?? true)
            <x-filament::dropdown.list.item
                x-on:click="$el.closest('.fi-dropdown-panel')?.close()"
                wire:click="mountAction('rename', { path: {{ \Illuminate\Support\Js::from($item->path) }} })"
                icon="heroicon-m-pencil"
            >
                {{ __('filament-file-manager::file-manager.actions.rename') }}
            </x-filament::dropdown.list.item>
        @endif
        @if ($permissions['canDelete'] ?? true)
            <x-filament::dropdown.list.item
                x-on:click="$el.closest('.fi-dropdown-panel')?.close()"
                wire:click="mountAction('deleteItem', { path: {{ \Illuminate\Support\Js::from($item->path) }} })"
                icon="heroicon-m-trash"
                color="danger"
            >
                {{ __('filament-file-manager::file-manager.actions.delete') }}
            </x-filament::dropdown.list.item>
        @endif
    </x-filament::dropdown.list>
</x-filament::dropdown>
