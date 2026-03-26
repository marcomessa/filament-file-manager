@php
    $pickMode = $pickMode ?? false;
    $multiple = $multiple ?? true;
    $permissions = $permissions ?? [];
    $isSelected = in_array($item->path, $selectedItems, true);
    $canMove = ! $pickMode && ($permissions['canMove'] ?? true);
@endphp

@if ($isFolder)
    <div
        @class([
            'group flex w-full items-center gap-4 px-4 py-2.5 transition duration-150',
            'bg-primary-50/50 dark:bg-primary-500/10' => $isSelected,
            'hover:bg-gray-50 dark:hover:bg-white/5' => !$isSelected,
        ])
        @if (! $pickMode)
            data-context-target
            @contextmenu.prevent="$el.closest('[x-ref=contextMenu]').__x.$data.show($event, @js($item->path), 'folder')"
        @endif
        @if ($canMove)
            x-data="{ dragOver: false }"
            draggable="true"
            @dragstart="$event.dataTransfer.setData('text/plain', @js($item->path))"
            @dragover.prevent="dragOver = true"
            @dragleave="dragOver = false"
            @drop.prevent="
                dragOver = false;
                const path = $event.dataTransfer.getData('text/plain');
                if (path && path !== @js($item->path)) {
                    $wire.moveItem(path, @js($item->path));
                }
            "
            :class="dragOver && 'ring-2 ring-inset ring-primary-500 bg-primary-50 dark:bg-primary-500/20'"
        @endif
    >
        @if (! $pickMode)
            {{-- Checkbox --}}
            <div class="w-8 shrink-0">
                <input
                    type="checkbox"
                    wire:model.live="selectedItems"
                    value="{{ $item->path }}"
                    class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5"
                    @click.stop
                />
            </div>
        @endif
        <button
            wire:click="navigateTo(@js($item->path))"
            type="button"
            class="flex flex-1 items-center gap-4 text-left"
        >
            <div class="flex size-8 shrink-0 items-center justify-center">
                <x-filament::icon icon="heroicon-o-folder" class="size-6 text-amber-400 transition group-hover:text-amber-500" />
            </div>
            <span class="flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $item->name }}
            </span>
        </button>
        <span class="w-24 text-sm text-gray-400 dark:text-gray-500">&mdash;</span>
        <span class="hidden w-24 text-sm text-gray-400 dark:text-gray-500 md:block">{{ __('filament-file-manager::file-manager.labels.folder') }}</span>
        @if (! $pickMode)
            <span class="hidden w-36 text-sm text-gray-400 dark:text-gray-500 lg:block">
                {{ $item->formattedDate() }}
            </span>
            <div class="flex w-24 items-center justify-end gap-1 opacity-0 transition group-hover:opacity-100">
                <x-filament-file-manager::file-actions :item="$item" :is-folder="true" size="md" :permissions="$permissions" />
            </div>
        @endif
    </div>
@else
    <div
        @class([
            'group flex items-center gap-4 px-4 py-2.5 transition duration-150',
            'bg-primary-50/50 dark:bg-primary-500/10' => $isSelected,
        ])
        @if (! $isSelected && ! $pickMode)
            :class="previewFile?.path === @js($item->path)
                ? 'bg-gray-100/60 dark:bg-white/[0.04]'
                : 'hover:bg-gray-50 dark:hover:bg-white/5'"
        @elseif (! $isSelected)
            class="hover:bg-gray-50 dark:hover:bg-white/5"
        @endif
        @if (! $pickMode)
            data-context-target
            @click.stop
            @contextmenu.prevent="$el.closest('[x-ref=contextMenu]').__x.$data.show($event, @js($item->path), 'file')"
        @endif
        @if ($canMove)
            draggable="true"
            @dragstart="$event.dataTransfer.setData('text/plain', @js($item->path))"
        @endif
    >
        {{-- Checkbox --}}
        @if (! $pickMode || $multiple)
            <div class="w-8 shrink-0">
                <input
                    type="checkbox"
                    wire:model.live="selectedItems"
                    value="{{ $item->path }}"
                    class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5"
                    @click.stop
                />
            </div>
        @else
            <div class="w-8 shrink-0"></div>
        @endif

        {{-- Thumbnail or Icon --}}
        <div class="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded">
            <x-filament-file-manager::file-thumbnail :item="$item" size="sm" />
        </div>

        {{-- Name --}}
        <button
            @if ($pickMode)
                wire:click="toggleSelection(@js($item->path))"
            @else
                @click="previewFile = @js($item->toPreviewArray())"
                @dblclick="$wire.mountAction('preview', { path: @js($item->path) })"
            @endif
            type="button"
            class="flex-1 truncate text-left text-sm text-gray-700 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400"
            title="{{ $item->name }}"
        >
            {{ $item->name }}
        </button>

        <span class="w-24 text-sm text-gray-400 dark:text-gray-500">
            {{ $item->formattedSize() }}
        </span>
        <span class="hidden w-24 text-sm uppercase text-gray-400 dark:text-gray-500 md:block">
            {{ $item->extension }}
        </span>
        @if (! $pickMode)
            <span class="hidden w-36 text-sm text-gray-400 dark:text-gray-500 lg:block">
                {{ $item->formattedDate() }}
            </span>
            <div class="flex w-24 items-center justify-end gap-1 opacity-0 transition group-hover:opacity-100">
                <x-filament-file-manager::file-actions :item="$item" size="md" :permissions="$permissions" />
            </div>
        @endif
    </div>
@endif
