<div
    x-data="{
        open: false,
        x: 0,
        y: 0,
        contextPath: null,
        contextType: null,
        show(event, path, type) {
            this.contextPath = path;
            this.contextType = type;
            this.x = event.clientX;
            this.y = event.clientY;

            // Ensure menu stays within viewport
            this.$nextTick(() => {
                const menu = this.$refs.menu;
                if (!menu) return;
                const rect = menu.getBoundingClientRect();
                if (this.x + rect.width > window.innerWidth) {
                    this.x = window.innerWidth - rect.width - 8;
                }
                if (this.y + rect.height > window.innerHeight) {
                    this.y = window.innerHeight - rect.height - 8;
                }
            });

            this.open = true;
        },
        close() {
            this.open = false;
            this.contextPath = null;
            this.contextType = null;
        }
    }"
    x-ref="contextMenu"
    @click.window="close()"
    @contextmenu.window="if (!$event.target.closest('[data-context-target]')) { show($event, null, 'background'); $event.preventDefault(); }"
    @keydown.escape.window="close()"
    class="contents"
>
    {{-- Menu dropdown --}}
    <div
        x-ref="menu"
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :style="{ position: 'fixed', left: x + 'px', top: y + 'px', zIndex: 50 }"
        class="w-48 rounded-xl bg-white py-1 shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10"
        @click="close()"
    >
        {{-- File actions --}}
        <template x-if="contextType === 'file'">
            <div>
                <button
                    @click="$wire.mountAction('preview', { path: contextPath })"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-m-eye" class="size-4 text-gray-400" />
                    {{ __('filament-file-manager::file-manager.actions.preview') }}
                </button>
                <button
                    @click="$wire.downloadFile(contextPath)"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-m-arrow-down-tray" class="size-4 text-gray-400" />
                    {{ __('filament-file-manager::file-manager.actions.download') }}
                </button>
                <div class="my-1 border-t border-gray-100 dark:border-white/5"></div>
                <button
                    @click="$wire.mountAction('rename', { path: contextPath })"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-m-pencil" class="size-4 text-gray-400" />
                    {{ __('filament-file-manager::file-manager.actions.rename') }}
                </button>
                <button
                    @click="$wire.mountAction('deleteItem', { path: contextPath })"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                >
                    <x-filament::icon icon="heroicon-m-trash" class="size-4" />
                    {{ __('filament-file-manager::file-manager.actions.delete') }}
                </button>
            </div>
        </template>

        {{-- Folder actions --}}
        <template x-if="contextType === 'folder'">
            <div>
                <button
                    @click="$wire.navigateTo(contextPath)"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-m-folder-open" class="size-4 text-gray-400" />
                    {{ __('filament-file-manager::file-manager.actions.open') }}
                </button>
                <div class="my-1 border-t border-gray-100 dark:border-white/5"></div>
                <button
                    @click="$wire.mountAction('rename', { path: contextPath })"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-m-pencil" class="size-4 text-gray-400" />
                    {{ __('filament-file-manager::file-manager.actions.rename') }}
                </button>
                <button
                    @click="$wire.mountAction('deleteItem', { path: contextPath })"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                >
                    <x-filament::icon icon="heroicon-m-trash" class="size-4" />
                    {{ __('filament-file-manager::file-manager.actions.delete') }}
                </button>
            </div>
        </template>

        {{-- Background actions --}}
        <template x-if="contextType === 'background'">
            <div>
                <button
                    @click="$wire.mountAction('uploadFiles')"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-m-arrow-up-tray" class="size-4 text-gray-400" />
                    {{ __('filament-file-manager::file-manager.actions.upload_files') }}
                </button>
                <button
                    @click="$wire.mountAction('createFolder')"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-m-folder-plus" class="size-4 text-gray-400" />
                    {{ __('filament-file-manager::file-manager.actions.new_folder') }}
                </button>
            </div>
        </template>
    </div>
</div>
