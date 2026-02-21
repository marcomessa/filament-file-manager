@php
    $pickMode = $pickMode ?? false;
    $multiple = $multiple ?? true;
    $isSelected = in_array($item->path, $selectedItems, true);
    $hasSelection = count($selectedItems) > 0;
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
            @if (! $pickMode)
                dragOver: false,
            @endif
        }"
        @if (! $pickMode)
            @mouseenter="showActions = true"
            @mouseleave="showActions = false"
            @contextmenu.prevent="$el.closest('[x-ref=contextMenu]').__x.$data.show($event, '{{ $item->path }}', 'folder')"
            draggable="true"
            @dragstart="$event.dataTransfer.setData('text/plain', '{{ $item->path }}')"
            @dragover.prevent="dragOver = true"
            @dragleave="dragOver = false"
            @drop.prevent="
                dragOver = false;
                const path = $event.dataTransfer.getData('text/plain');
                if (path && path !== '{{ $item->path }}') {
                    $wire.moveItem(path, '{{ $item->path }}');
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
                    wire:click="toggleSelection('{{ $item->path }}')"
                    @checked($isSelected)
                    class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5"
                    @click.stop
                />
            </div>
        @endif

        <button
            wire:click="navigateTo('{{ $item->path }}')"
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
                <button
                    wire:click="mountAction('rename', { path: '{{ $item->path }}' })"
                    type="button"
                    class="flex size-6 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                    title="{{ __('filament-file-manager::file-manager.actions.rename') }}"
                >
                    <x-filament::icon icon="heroicon-m-pencil" class="size-3.5" />
                </button>
                <button
                    wire:click="mountAction('deleteItem', { path: '{{ $item->path }}' })"
                    type="button"
                    class="flex size-6 items-center justify-center rounded-md text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10"
                    title="{{ __('filament-file-manager::file-manager.actions.delete') }}"
                >
                    <x-filament::icon icon="heroicon-m-trash" class="size-3.5" />
                </button>
            </div>
        @endif
    </div>
@else
    <div
        @class([
            'group relative flex flex-col items-center gap-2 rounded-xl p-3 transition duration-200',
            'ring-1 ring-primary-500 bg-primary-50/50 dark:bg-primary-500/10 dark:ring-primary-400' => $isSelected,
        ])
        @if (! $isSelected)
            :class="previewFile?.path === '{{ $item->path }}'
                ? 'ring-2 ring-gray-400 dark:ring-gray-500'
                : 'ring-1 ring-gray-950/5 hover:shadow-md hover:scale-[1.02] dark:ring-white/10 dark:hover:ring-white/20'"
        @endif
        @if (! $pickMode)
            data-context-target
        @endif
        x-data="{
            showActions: false,
            thumbnailUrl: @js($item->thumbnailUrl),
            loading: false,
        }"
        @if (! $pickMode)
            @click.stop
            @mouseenter="showActions = true"
            @mouseleave="showActions = false"
            @contextmenu.prevent="$el.closest('[x-ref=contextMenu]').__x.$data.show($event, '{{ $item->path }}', 'file')"
            draggable="true"
            @dragstart="$event.dataTransfer.setData('text/plain', '{{ $item->path }}')"
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
                    wire:click="toggleSelection('{{ $item->path }}')"
                    @checked($isSelected)
                    class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5"
                    @click.stop
                />
            </div>
        @endif

        {{-- Clickable area --}}
        <button
            @if ($pickMode)
                wire:click="toggleSelection('{{ $item->path }}')"
            @else
                @click="previewFile = @js($item->toPreviewArray())"
                @dblclick="$wire.mountAction('preview', { path: '{{ $item->path }}' })"
            @endif
            type="button"
            class="flex w-full flex-col items-center gap-2"
        >
            {{-- Thumbnail / Icon area --}}
            <div class="flex aspect-square w-full items-center justify-center overflow-hidden rounded-lg bg-gray-50 dark:bg-white/5">
                @if ($item->hasThumbnail())
                    <img
                        src="{{ $item->thumbnailUrl }}"
                        alt="{{ $item->name }}"
                        class="size-full object-cover"
                        loading="lazy"
                    />
                @elseif ($item->isThumbnailable() && $item->url !== null)
                    <template x-if="thumbnailUrl">
                        <img
                            :src="thumbnailUrl"
                            alt="{{ $item->name }}"
                            class="size-full object-cover"
                            loading="lazy"
                        />
                    </template>
                    <template x-if="!thumbnailUrl && !loading">
                        <div
                            x-intersect.once="
                                loading = true;
                                $wire.generateThumbnail('{{ $item->path }}').then(url => {
                                    if (url) { thumbnailUrl = url; }
                                    loading = false;
                                })
                            "
                            class="flex size-full items-center justify-center"
                        >
                            <x-filament::icon :icon="$item->category->icon()" @class(['size-10', $item->category->color()]) />
                        </div>
                    </template>
                    <template x-if="!thumbnailUrl && loading">
                        <div class="flex size-full items-center justify-center">
                            <div class="size-8 animate-pulse rounded-full bg-gray-200 dark:bg-white/10"></div>
                        </div>
                    </template>
                @else
                    <x-filament::icon :icon="$item->category->icon()" @class(['size-10', $item->category->color()]) />
                @endif
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
                <button
                    wire:click="mountAction('preview', { path: '{{ $item->path }}' })"
                    type="button"
                    class="flex size-6 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                    title="{{ __('filament-file-manager::file-manager.actions.preview') }}"
                >
                    <x-filament::icon icon="heroicon-m-eye" class="size-3.5" />
                </button>
                <button
                    wire:click="downloadFile('{{ $item->path }}')"
                    type="button"
                    class="flex size-6 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                    title="{{ __('filament-file-manager::file-manager.actions.download') }}"
                >
                    <x-filament::icon icon="heroicon-m-arrow-down-tray" class="size-3.5" />
                </button>
                <button
                    wire:click="mountAction('rename', { path: '{{ $item->path }}' })"
                    type="button"
                    class="flex size-6 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                    title="{{ __('filament-file-manager::file-manager.actions.rename') }}"
                >
                    <x-filament::icon icon="heroicon-m-pencil" class="size-3.5" />
                </button>
                <button
                    wire:click="mountAction('deleteItem', { path: '{{ $item->path }}' })"
                    type="button"
                    class="flex size-6 items-center justify-center rounded-md text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10"
                    title="{{ __('filament-file-manager::file-manager.actions.delete') }}"
                >
                    <x-filament::icon icon="heroicon-m-trash" class="size-3.5" />
                </button>
            </div>
        @endif
    </div>
@endif
