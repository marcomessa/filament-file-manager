@php
    $pickMode = $pickMode ?? false;
    $multiple = $multiple ?? true;
    $permissions = $permissions ?? [];
    $isSelected = in_array($item->path, $selectedItems, true);
    $hasSelection = count($selectedItems) > 0;
    $canMove = ! $pickMode && ($permissions['canMove'] ?? true);
@endphp

@if ($isFolder)
    <div
        @class([
            'group relative flex flex-col items-center gap-2 rounded-xl p-3 ring-1 transition duration-200',
            'ring-primary-500 bg-primary-50/50 dark:bg-primary-500/10 dark:ring-primary-400' => $isSelected,
            'ring-gray-950/5 hover:shadow-md hover:scale-[1.02] dark:ring-white/10 dark:hover:ring-white/20' => !$isSelected,
        ])
        @if (! $pickMode)
            data-context-target
        @endif
        x-data="{
            showActions: false,
            @if ($canMove)
                dragOver: false,
            @endif
        }"
        @if (! $pickMode)
            @mouseenter="showActions = true"
            @mouseleave="showActions = false"
            @contextmenu.prevent="$el.closest('[x-ref=contextMenu]').__x.$data.show($event, @js($item->path), 'folder')"
        @endif
        @if ($canMove)
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
            :class="dragOver && 'ring-2 ring-primary-500 scale-105 bg-primary-50 dark:bg-primary-500/20'"
        @endif
    >
        @if (! $pickMode)
            {{-- Selection checkbox --}}
            <div
                @class([
                    'absolute top-1.5 left-1.5 z-10',
                    'opacity-0 group-hover:opacity-100' => !$hasSelection && !$isSelected,
                ])
            >
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
            class="flex w-full flex-col items-center gap-2"
        >
            <div class="flex size-16 items-center justify-center">
                <x-filament::icon icon="heroicon-o-folder" class="size-12 text-amber-400 transition group-hover:text-amber-500" />
            </div>
            <span class="max-w-full truncate text-center text-xs font-medium text-gray-700 dark:text-gray-300" title="{{ $item->name }}">
                {{ $item->name }}
            </span>
        </button>

        @if (! $pickMode)
            {{-- Actions overlay --}}
            <div
                x-show="showActions"
                x-transition.opacity.duration.150ms
                class="absolute top-1.5 right-1.5 flex items-center gap-0.5 rounded-lg bg-white/90 p-0.5 shadow-sm ring-1 ring-gray-950/5 backdrop-blur-sm dark:bg-gray-800/90 dark:ring-white/10"
            >
                <x-filament-file-manager::file-actions :item="$item" :is-folder="true" size="sm" :permissions="$permissions" />
            </div>
        @endif
    </div>
@else
    <div
        @class([
            'group relative flex flex-col items-center gap-2 rounded-xl p-3 transition duration-200',
            'ring-1 ring-primary-500 bg-primary-50/50 dark:bg-primary-500/10 dark:ring-primary-400' => $isSelected,
        ])
        @if (! $isSelected && ! $pickMode)
            :class="previewFile?.path === @js($item->path)
                ? 'ring-2 ring-gray-400 dark:ring-gray-500'
                : 'ring-1 ring-gray-950/5 hover:shadow-md hover:scale-[1.02] dark:ring-white/10 dark:hover:ring-white/20'"
        @elseif (! $isSelected)
            class="ring-1 ring-gray-950/5 hover:shadow-md hover:scale-[1.02] dark:ring-white/10 dark:hover:ring-white/20"
        @endif
        @if (! $pickMode)
            data-context-target
        @endif
        x-data="{ showActions: false }"
        @if (! $pickMode)
            @click.stop
            @mouseenter="showActions = true"
            @mouseleave="showActions = false"
            @contextmenu.prevent="$el.closest('[x-ref=contextMenu]').__x.$data.show($event, @js($item->path), 'file')"
        @endif
        @if ($canMove)
            draggable="true"
            @dragstart="$event.dataTransfer.setData('text/plain', @js($item->path))"
        @endif
    >
        @if (! $pickMode || $multiple)
            {{-- Selection checkbox --}}
            <div
                @class([
                    'absolute top-1.5 left-1.5 z-10',
                    'opacity-0 group-hover:opacity-100' => !$hasSelection && !$isSelected,
                ])
            >
                <input
                    type="checkbox"
                    wire:model.live="selectedItems"
                    value="{{ $item->path }}"
                    class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5"
                    @click.stop
                />
            </div>
        @endif

        {{-- Clickable area --}}
        <button
            @if ($pickMode)
                wire:click="toggleSelection(@js($item->path))"
            @else
                @click="previewFile = @js($item->toPreviewArray())"
                @dblclick="$wire.mountAction('preview', { path: @js($item->path) })"
            @endif
            type="button"
            class="flex w-full flex-col items-center gap-2"
        >
            {{-- Thumbnail / Icon area --}}
            <div class="flex aspect-square w-full items-center justify-center overflow-hidden rounded-lg bg-gray-50 dark:bg-white/5">
                <x-filament-file-manager::file-thumbnail :item="$item" size="lg" />
            </div>

            {{-- File info --}}
            <div class="flex w-full flex-col items-center gap-0.5">
                <span class="max-w-full truncate text-center text-xs font-medium text-gray-700 dark:text-gray-300" title="{{ $item->name }}">
                    {{ $item->name }}
                </span>
                <span class="text-[10px] text-gray-400 dark:text-gray-500">
                    {{ $item->formattedSize() }}
                </span>
            </div>
        </button>

        @if (! $pickMode)
            {{-- Actions overlay --}}
            <div
                x-show="showActions"
                x-transition.opacity.duration.150ms
                class="absolute top-1.5 right-1.5 flex items-center gap-0.5 rounded-lg bg-white/90 p-0.5 shadow-sm ring-1 ring-gray-950/5 backdrop-blur-sm dark:bg-gray-800/90 dark:ring-white/10"
            >
                <x-filament-file-manager::file-actions :item="$item" size="sm" :permissions="$permissions" />
            </div>
        @endif
    </div>
@endif
