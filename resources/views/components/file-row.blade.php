@php
    $pickMode = $pickMode ?? false;
    $multiple = $multiple ?? true;
    $isSelected = in_array($item->path, $selectedItems, true);
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
            x-data="{ dragOver: false }"
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
            :class="dragOver && 'ring-2 ring-inset ring-primary-500 bg-primary-50 dark:bg-primary-500/20'"
        @endif
    >
        @if (! $pickMode)
            {{-- Checkbox --}}
            <div class="w-8 shrink-0">
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
                <button
                    wire:click="mountAction('rename', { path: '{{ $item->path }}' })"
                    type="button"
                    class="flex size-7 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                    title="{{ __('filament-file-manager::file-manager.actions.rename') }}"
                >
                    <x-filament::icon icon="heroicon-m-pencil" class="size-4" />
                </button>
                <button
                    wire:click="mountAction('deleteItem', { path: '{{ $item->path }}' })"
                    type="button"
                    class="flex size-7 items-center justify-center rounded-md text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10"
                    title="{{ __('filament-file-manager::file-manager.actions.delete') }}"
                >
                    <x-filament::icon icon="heroicon-m-trash" class="size-4" />
                </button>
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
            :class="previewFile?.path === '{{ $item->path }}'
                ? 'bg-gray-100/60 dark:bg-white/[0.04]'
                : 'hover:bg-gray-50 dark:hover:bg-white/5'"
        @elseif (! $isSelected)
            class="hover:bg-gray-50 dark:hover:bg-white/5"
        @endif
        @if (! $pickMode)
            data-context-target
            @click.stop
            @contextmenu.prevent="$el.closest('[x-ref=contextMenu]').__x.$data.show($event, '{{ $item->path }}', 'file')"
            draggable="true"
            @dragstart="$event.dataTransfer.setData('text/plain', '{{ $item->path }}')"
        @endif
        x-data="{ thumbnailUrl: @js($item->thumbnailUrl), loading: false }"
    >
        {{-- Checkbox --}}
        @if (! $pickMode || $multiple)
            <div class="w-8 shrink-0">
                <input
                    type="checkbox"
                    wire:click="toggleSelection('{{ $item->path }}')"
                    @checked($isSelected)
                    class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5"
                    @click.stop
                />
            </div>
        @else
            <div class="w-8 shrink-0"></div>
        @endif

        {{-- Thumbnail or Icon --}}
        <div class="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded">
            @if ($item->hasThumbnail())
                <img
                    src="{{ $item->thumbnailUrl }}"
                    alt="{{ $item->name }}"
                    class="size-8 rounded object-cover"
                    loading="lazy"
                />
            @elseif ($item->isThumbnailable() && $item->url !== null)
                <template x-if="thumbnailUrl">
                    <img
                        :src="thumbnailUrl"
                        alt="{{ $item->name }}"
                        class="size-8 rounded object-cover"
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
                    >
                        <x-filament::icon :icon="$item->category->icon()" @class(['size-5', $item->category->color()]) />
                    </div>
                </template>
                <template x-if="!thumbnailUrl && loading">
                    <div class="size-5 animate-pulse rounded bg-gray-200 dark:bg-white/10"></div>
                </template>
            @else
                <x-filament::icon :icon="$item->category->icon()" @class(['size-5', $item->category->color()]) />
            @endif
        </div>

        {{-- Name --}}
        <button
            @if ($pickMode)
                wire:click="toggleSelection('{{ $item->path }}')"
            @else
                @click="previewFile = @js($item->toPreviewArray())"
                @dblclick="$wire.mountAction('preview', { path: '{{ $item->path }}' })"
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
                <button
                    wire:click="mountAction('preview', { path: '{{ $item->path }}' })"
                    type="button"
                    class="flex size-7 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                    title="{{ __('filament-file-manager::file-manager.actions.preview') }}"
                >
                    <x-filament::icon icon="heroicon-m-eye" class="size-4" />
                </button>
                <button
                    wire:click="downloadFile('{{ $item->path }}')"
                    type="button"
                    class="flex size-7 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                    title="{{ __('filament-file-manager::file-manager.actions.download') }}"
                >
                    <x-filament::icon icon="heroicon-m-arrow-down-tray" class="size-4" />
                </button>
                <button
                    wire:click="mountAction('rename', { path: '{{ $item->path }}' })"
                    type="button"
                    class="flex size-7 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                    title="{{ __('filament-file-manager::file-manager.actions.rename') }}"
                >
                    <x-filament::icon icon="heroicon-m-pencil" class="size-4" />
                </button>
                <button
                    wire:click="mountAction('deleteItem', { path: '{{ $item->path }}' })"
                    type="button"
                    class="flex size-7 items-center justify-center rounded-md text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10"
                    title="{{ __('filament-file-manager::file-manager.actions.delete') }}"
                >
                    <x-filament::icon icon="heroicon-m-trash" class="size-4" />
                </button>
            </div>
        @endif
    </div>
@endif
