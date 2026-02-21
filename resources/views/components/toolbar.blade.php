@if (count($selectedItems) > 0)
    {{-- Bulk action bar --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ trans_choice('filament-file-manager::file-manager.toolbar.selected_count', count($selectedItems), ['count' => count($selectedItems)]) }}
            </span>

            {{ $this->deleteSelectedAction }}
            {{ $this->moveSelectedAction }}
        </div>

        <button
            wire:click="clearSelection"
            type="button"
            class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-gray-300"
        >
            <x-filament::icon icon="heroicon-m-x-mark" class="size-4" />
            {{ __('filament-file-manager::file-manager.toolbar.deselect') }}
        </button>
    </div>
@else
    {{-- Normal toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            {{-- Upload --}}
            {{ $this->uploadFilesAction }}

            {{-- New folder --}}
            {{ $this->createFolderAction }}

            {{-- Refresh --}}
            {{ $this->refreshAction }}
        </div>

        <div class="flex items-center gap-2">
            {{-- Sort dropdown --}}
            <select
                wire:change="setSortField($event.target.value)"
                class="fm-select"
            >
                <option value="name" @selected($sortField === 'name')>{{ __('filament-file-manager::file-manager.toolbar.sort_name') }}</option>
                <option value="size" @selected($sortField === 'size')>{{ __('filament-file-manager::file-manager.toolbar.sort_size') }}</option>
                <option value="date" @selected($sortField === 'date')>{{ __('filament-file-manager::file-manager.toolbar.sort_date') }}</option>
                <option value="type" @selected($sortField === 'type')>{{ __('filament-file-manager::file-manager.toolbar.sort_type') }}</option>
            </select>

            {{-- Sort direction toggle --}}
            <button
                wire:click="setSortField('{{ $sortField }}')"
                type="button"
                class="flex size-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-300"
                title="{{ $sortDirection === 'asc' ? __('filament-file-manager::file-manager.toolbar.sort_asc') : __('filament-file-manager::file-manager.toolbar.sort_desc') }}"
            >
                <x-filament::icon
                    :icon="$sortDirection === 'asc' ? 'heroicon-m-bars-arrow-up' : 'heroicon-m-bars-arrow-down'"
                    class="size-5"
                />
            </button>

            {{-- View mode toggle --}}
            <div class="flex items-center rounded-lg bg-gray-100 p-0.5 dark:bg-white/5">
                <button
                    wire:click="setViewMode('grid')"
                    type="button"
                    @class([
                        'flex size-8 items-center justify-center rounded-md transition',
                        'bg-white text-primary-600 shadow-sm dark:bg-gray-700 dark:text-primary-400' => $viewMode === 'grid',
                        'text-gray-400 hover:text-gray-500 dark:hover:text-gray-300' => $viewMode !== 'grid',
                    ])
                >
                    <x-filament::icon icon="heroicon-m-squares-2x2" class="size-4" />
                </button>
                <button
                    wire:click="setViewMode('list')"
                    type="button"
                    @class([
                        'flex size-8 items-center justify-center rounded-md transition',
                        'bg-white text-primary-600 shadow-sm dark:bg-gray-700 dark:text-primary-400' => $viewMode === 'list',
                        'text-gray-400 hover:text-gray-500 dark:hover:text-gray-300' => $viewMode !== 'list',
                    ])
                >
                    <x-filament::icon icon="heroicon-m-list-bullet" class="size-4" />
                </button>
            </div>
        </div>
    </div>
@endif
