<div class="flex h-full flex-col gap-4">
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-3">
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

    {{-- Breadcrumbs --}}
    @include('filament-file-manager::components.breadcrumbs')

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        @if ($listing && !$listing->isEmpty())
            @if ($viewMode === 'grid')
                <div class="grid grid-cols-2 gap-4 p-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    @foreach ($listing->folders as $folder)
                        @include('filament-file-manager::components.file-card', ['item' => $folder, 'isFolder' => true, 'pickMode' => true, 'multiple' => $multiple])
                    @endforeach

                    @foreach ($listing->files as $file)
                        @include('filament-file-manager::components.file-card', ['item' => $file, 'isFolder' => false, 'pickMode' => true, 'multiple' => $multiple])
                    @endforeach
                </div>
            @else
                <div class="divide-y divide-gray-200 dark:divide-white/10">
                    {{-- List header --}}
                    <div class="flex items-center gap-4 px-4 py-2 text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                        <div class="w-8 shrink-0"></div>
                        <button wire:click="setSortField('name')" class="flex flex-1 items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('filament-file-manager::file-manager.toolbar.sort_name') }}
                            @if ($sortField === 'name')
                                <x-filament::icon :icon="$sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down'" class="size-3" />
                            @endif
                        </button>
                        <button wire:click="setSortField('size')" class="flex w-24 items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('filament-file-manager::file-manager.toolbar.sort_size') }}
                            @if ($sortField === 'size')
                                <x-filament::icon :icon="$sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down'" class="size-3" />
                            @endif
                        </button>
                        <button wire:click="setSortField('type')" class="hidden w-24 items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200 md:flex">
                            {{ __('filament-file-manager::file-manager.toolbar.sort_type') }}
                            @if ($sortField === 'type')
                                <x-filament::icon :icon="$sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down'" class="size-3" />
                            @endif
                        </button>
                    </div>

                    @foreach ($listing->folders as $folder)
                        @include('filament-file-manager::components.file-row', ['item' => $folder, 'isFolder' => true, 'pickMode' => true, 'multiple' => $multiple])
                    @endforeach

                    @foreach ($listing->files as $file)
                        @include('filament-file-manager::components.file-row', ['item' => $file, 'isFolder' => false, 'pickMode' => true, 'multiple' => $multiple])
                    @endforeach
                </div>
            @endif

            {{-- Status bar --}}
            <div class="border-t border-gray-200 px-4 py-2 text-xs text-gray-400 dark:border-white/10 dark:text-gray-500">
                @php
                    $folderCount = count($listing->folders);
                    $fileCount = count($listing->files);
                @endphp
                @if (count($selectedItems) > 0)
                    <span class="font-medium text-primary-600 dark:text-primary-400">
                        @if ($multiple)
                            {{ __('filament-file-manager::file-manager.labels.selected', ['count' => count($selectedItems)]) }}
                        @else
                            {{ __('filament-file-manager::file-manager.labels.file_selected') }}
                        @endif
                    </span> &mdash;
                @endif
                {{ trans_choice('filament-file-manager::file-manager.labels.files_count', $fileCount, ['count' => $fileCount]) }}{{ $folderCount > 0 ? ', ' . trans_choice('filament-file-manager::file-manager.labels.folders_count', $folderCount, ['count' => $folderCount]) : '' }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 p-16 text-gray-400 dark:text-gray-500">
                <x-filament::icon icon="heroicon-o-folder-open" class="size-12" />
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament-file-manager::file-manager.misc.empty_folder') }}</p>
            </div>
        @endif
    </div>

    {{-- Confirm button --}}
    <div class="flex shrink-0 items-center justify-end border-t border-gray-200 pt-4 dark:border-white/10">
        <button
            wire:click="confirmSelection"
            type="button"
            @disabled(count($selectedItems) === 0)
            @class([
                'inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-semibold shadow-sm transition',
                'bg-primary-600 text-white hover:bg-primary-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-500 dark:hover:bg-primary-400' => count($selectedItems) > 0,
                'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500' => count($selectedItems) === 0,
            ])
        >
            <x-filament::icon icon="heroicon-m-check" class="size-4" />
            {{ __('filament-file-manager::file-manager.actions.confirm_selection') }}
            @if (count($selectedItems) > 0)
                ({{ count($selectedItems) }})
            @endif
        </button>
    </div>
</div>
