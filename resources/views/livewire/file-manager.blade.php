<div
    x-data="{
        previewFile: null,
        init() {
            $wire.$watch('currentPath', () => { this.previewFile = null; });
        },
        handleKeydown(e) {
            // Escape: close sidebar preview, or clear selection
            if (e.key === 'Escape') {
                if (this.previewFile) {
                    this.previewFile = null;
                    return;
                }
                $wire.clearSelection();
                return;
            }

            // Ctrl+A / Cmd+A: select all
            if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
                e.preventDefault();
                $wire.selectAll();
                return;
            }

            // Delete/Backspace: delete selected
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if ($wire.selectedItems.length > 0 && !e.target.closest('input, textarea, [contenteditable]')) {
                    e.preventDefault();
                    $wire.mountAction('deleteSelected');
                    return;
                }
            }

            // F2: rename single selected item
            if (e.key === 'F2') {
                if ($wire.selectedItems.length === 1) {
                    e.preventDefault();
                    $wire.mountAction('rename', { path: $wire.selectedItems[0] });
                }
            }
        }
    }"
    @keydown.window="handleKeydown($event)"
    class="flex gap-4"
>
    {{-- Main content --}}
    <div class="flex min-w-0 flex-1 flex-col gap-4">
        {{-- Toolbar --}}
        @include('filament-file-manager::components.toolbar')

        {{-- Breadcrumbs --}}
        @include('filament-file-manager::components.breadcrumbs')

        {{-- Content --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" @click="previewFile = null">
            @if ($listing && !$listing->isEmpty())
                @if ($viewMode === 'grid')
                    <div class="grid grid-cols-2 gap-4 p-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                        @foreach ($listing->folders as $folder)
                            @include('filament-file-manager::components.file-card', ['item' => $folder, 'isFolder' => true])
                        @endforeach

                        @foreach ($listing->files as $file)
                            @include('filament-file-manager::components.file-card', ['item' => $file, 'isFolder' => false])
                        @endforeach
                    </div>
                @else
                    <div class="divide-y divide-gray-200 dark:divide-white/10">
                        {{-- List header --}}
                        <div class="flex items-center gap-4 px-4 py-2 text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                            <div class="w-8 shrink-0">
                                <input
                                    type="checkbox"
                                    @change="$event.target.checked ? $wire.selectAll() : $wire.clearSelection()"
                                    :checked="$wire.selectedItems.length > 0 && $wire.selectedItems.length === {{ count($listing->folders) + count($listing->files) }}"
                                    class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5"
                                />
                            </div>
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
                            <button wire:click="setSortField('date')" class="hidden w-36 items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200 lg:flex">
                                {{ __('filament-file-manager::file-manager.toolbar.sort_date') }}
                                @if ($sortField === 'date')
                                    <x-filament::icon :icon="$sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down'" class="size-3" />
                                @endif
                            </button>
                            <div class="w-24"></div>
                        </div>

                        @foreach ($listing->folders as $folder)
                            @include('filament-file-manager::components.file-row', ['item' => $folder, 'isFolder' => true])
                        @endforeach

                        @foreach ($listing->files as $file)
                            @include('filament-file-manager::components.file-row', ['item' => $file, 'isFolder' => false])
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
                        <span class="font-medium text-primary-600 dark:text-primary-400">{{ __('filament-file-manager::file-manager.labels.selected', ['count' => count($selectedItems)]) }}</span> &mdash;
                    @endif
                    {{ trans_choice('filament-file-manager::file-manager.labels.files_count', $fileCount, ['count' => $fileCount]) }}{{ $folderCount > 0 ? ', ' . trans_choice('filament-file-manager::file-manager.labels.folders_count', $folderCount, ['count' => $folderCount]) : '' }}
                </div>
            @else
                <div class="flex flex-col items-center justify-center gap-3 p-16 text-gray-400 dark:text-gray-500">
                    <x-filament::icon icon="heroicon-o-folder-open" class="size-12" />
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament-file-manager::file-manager.misc.empty_folder') }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ __('filament-file-manager::file-manager.misc.empty_folder_hint') }}</p>
                </div>
            @endif
        </div>

        {{-- Context menu --}}
        @include('filament-file-manager::components.context-menu')
    </div>

    {{-- Preview sidebar --}}
    <div class="hidden overflow-hidden rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 lg:flex">
        @include('filament-file-manager::components.file-preview-sidebar')
    </div>

    <x-filament-actions::modals />
</div>
