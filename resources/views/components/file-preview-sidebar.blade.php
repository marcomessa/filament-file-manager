<div class="flex w-80 shrink-0" x-data="{ showInfo: true }">
    {{-- Empty state --}}
    <div
        x-show="!previewFile"
        class="flex w-full flex-col items-center justify-center gap-3 bg-white p-8 text-center dark:bg-gray-900"
    >
        <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="size-10 text-gray-300 dark:text-gray-600" />
        <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('filament-file-manager::file-manager.misc.select_file_preview') }}</p>
    </div>

    {{-- Preview content --}}
    <div
        x-show="previewFile"
        x-cloak
        class="flex w-full flex-col border-l border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <h3
                class="truncate text-sm font-semibold text-gray-900 dark:text-white"
                x-text="previewFile?.name"
            ></h3>
            <button
                @click="previewFile = null"
                type="button"
                class="flex size-7 shrink-0 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
            >
                <x-filament::icon icon="heroicon-m-x-mark" class="size-4" />
            </button>
        </div>

        {{-- Scrollable content --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Preview area --}}
            <div class="flex aspect-square items-center justify-center overflow-hidden bg-gray-50 p-4 dark:bg-white/5">
                {{-- Image preview (full URL or thumbnail) --}}
                <template x-if="previewFile?.isImage && previewFile?.url">
                    <img
                        :src="previewFile.url"
                        :alt="previewFile.name"
                        class="max-h-full max-w-full rounded-lg object-contain"
                    />
                </template>
                <template x-if="previewFile?.thumbnailUrl && !(previewFile?.isImage && previewFile?.url)">
                    <img
                        :src="previewFile.thumbnailUrl"
                        :alt="previewFile.name"
                        class="max-h-full max-w-full rounded-lg object-contain"
                    />
                </template>

                {{-- Category icon for non-images --}}
                <template x-if="!previewFile?.isImage && !previewFile?.thumbnailUrl">
                    <div class="flex flex-col items-center gap-3">
                        <x-filament::icon x-show="previewFile?.category === 'image'" icon="heroicon-o-photo" class="size-16 text-purple-500" />
                        <x-filament::icon x-show="previewFile?.category === 'document'" icon="heroicon-o-document-text" class="size-16 text-blue-500" />
                        <x-filament::icon x-show="previewFile?.category === 'audio'" icon="heroicon-o-musical-note" class="size-16 text-green-500" />
                        <x-filament::icon x-show="previewFile?.category === 'video'" icon="heroicon-o-film" class="size-16 text-red-500" />
                        <x-filament::icon x-show="previewFile?.category === 'archive'" icon="heroicon-o-archive-box" class="size-16 text-yellow-500" />
                        <x-filament::icon x-show="previewFile?.category === 'code'" icon="heroicon-o-code-bracket" class="size-16 text-gray-500" />
                        <x-filament::icon x-show="previewFile?.category === 'other'" icon="heroicon-o-document" class="size-16 text-gray-400" />
                        <span
                            class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500"
                            x-text="previewFile?.extension?.toUpperCase()"
                        ></span>
                    </div>
                </template>
            </div>

            {{-- File name & basic info --}}
            <div class="space-y-1 border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <p class="break-all text-sm font-medium text-gray-900 dark:text-white" x-text="previewFile?.name"></p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <span x-text="previewFile?.formattedSize"></span> &middot; <span x-text="previewFile?.extension?.toUpperCase()"></span>
                </p>
            </div>

            {{-- Collapsible info section --}}
            <div class="border-b border-gray-200 dark:border-white/10">
                <button
                    @click="showInfo = !showInfo"
                    type="button"
                    class="flex w-full items-center justify-between px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    {{ __('filament-file-manager::file-manager.misc.info') }}
                    <x-filament::icon
                        icon="heroicon-m-chevron-down"
                        class="size-4 transition-transform duration-200"
                        x-bind:class="showInfo ? 'rotate-180' : ''"
                    />
                </button>

                <div x-show="showInfo" x-collapse>
                    <dl class="space-y-2 px-4 pb-3 text-xs">
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('filament-file-manager::file-manager.misc.type') }}</dt>
                            <dd class="font-medium text-gray-700 dark:text-gray-300" x-text="previewFile?.categoryLabel"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('filament-file-manager::file-manager.misc.mime') }}</dt>
                            <dd class="font-medium text-gray-700 dark:text-gray-300" x-text="previewFile?.mimeType"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('filament-file-manager::file-manager.misc.size') }}</dt>
                            <dd class="font-medium text-gray-700 dark:text-gray-300" x-text="previewFile?.formattedSize"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('filament-file-manager::file-manager.misc.modified') }}</dt>
                            <dd class="font-medium text-gray-700 dark:text-gray-300" x-text="previewFile?.formattedDate"></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-1 border-t border-gray-200 px-3 py-2.5 dark:border-white/10">
            <button
                @click="$wire.mountAction('preview', { path: previewFile.path })"
                type="button"
                class="flex size-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                title="{{ __('filament-file-manager::file-manager.actions.full_preview') }}"
            >
                <x-filament::icon icon="heroicon-m-arrows-pointing-out" class="size-4" />
            </button>
            <button
                @click="$wire.downloadFile(previewFile.path)"
                type="button"
                class="flex size-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                title="{{ __('filament-file-manager::file-manager.actions.download') }}"
            >
                <x-filament::icon icon="heroicon-m-arrow-down-tray" class="size-4" />
            </button>
            <button
                @click="$wire.mountAction('rename', { path: previewFile.path })"
                type="button"
                class="flex size-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
                title="{{ __('filament-file-manager::file-manager.actions.rename') }}"
            >
                <x-filament::icon icon="heroicon-m-pencil" class="size-4" />
            </button>
            <button
                @click="$wire.mountAction('deleteItem', { path: previewFile.path })"
                type="button"
                class="flex size-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10"
                title="{{ __('filament-file-manager::file-manager.actions.delete') }}"
            >
                <x-filament::icon icon="heroicon-m-trash" class="size-4" />
            </button>
        </div>
    </div>
</div>
