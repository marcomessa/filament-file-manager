@php
    $fieldWrapperView = $getFieldWrapperView();
    $key = $getKey();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-data="{
            selectedPath: $wire.$entangle('{{ $statePath }}'),
            expandedFolders: {},
            childrenCache: {},
            loading: {},

            async fetchChildren(path) {
                if (this.childrenCache[path] !== undefined) {
                    return;
                }

                this.loading[path] = true;

                try {
                    const result = await $wire.callSchemaComponentMethod(
                        @js($key),
                        'getSubfolders',
                        { path },
                    );
                    this.childrenCache[path] = result;
                } catch (e) {
                    this.childrenCache[path] = [];
                } finally {
                    delete this.loading[path];
                }
            },

            async toggleFolder(path) {
                if (this.expandedFolders[path]) {
                    delete this.expandedFolders[path];
                    return;
                }

                this.expandedFolders[path] = true;
                await this.fetchChildren(path);
            },

            selectFolder(path) {
                this.selectedPath = path;
            },

            isSelected(path) {
                return this.selectedPath === path;
            },

            isLoading(path) {
                return !!this.loading[path];
            },

            getVisibleNodes() {
                const nodes = [];
                this._collectNodes('', 1, nodes);
                return nodes;
            },

            _collectNodes(parentPath, depth, result) {
                const children = this.childrenCache[parentPath];
                if (!children) return;

                for (const child of children) {
                    result.push({ name: child.name, path: child.path, depth });

                    if (this.expandedFolders[child.path]) {
                        if (this.childrenCache[child.path] !== undefined) {
                            this._collectNodes(child.path, depth + 1, result);
                        }
                    }
                }
            },

            showLoadingSkeleton(path) {
                return this.expandedFolders[path] && this.loading[path];
            },

            showEmptyState(path) {
                return this.expandedFolders[path]
                    && this.childrenCache[path] !== undefined
                    && this.childrenCache[path].length === 0;
            },
        }"
        x-init="await fetchChildren('')"
        class="rounded-lg border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900"
    >
        {{-- Scrollable tree area --}}
        <div class="max-h-60 overflow-y-auto p-1">
            {{-- Root item --}}
            <button
                type="button"
                x-on:click="selectFolder('')"
                :class="{
                    'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400': isSelected(''),
                    'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5': !isSelected(''),
                }"
                class="flex w-full items-center gap-1.5 rounded-md px-2 py-1.5 text-sm transition"
            >
                <x-filament::icon
                    icon="heroicon-m-home"
                    class="size-4 shrink-0 text-gray-400 dark:text-gray-500"
                />
                <span class="font-medium">{{ __('filament-file-manager::file-manager.labels.root') }}</span>
            </button>

            {{-- Loading state for root --}}
            <template x-if="isLoading('')">
                <div class="py-1 pl-8 pr-2">
                    <div class="h-4 w-24 animate-pulse rounded bg-gray-200 dark:bg-white/10"></div>
                </div>
            </template>

            {{-- Flat-rendered tree nodes --}}
            <template x-for="node in getVisibleNodes()" :key="node.path">
                <div>
                    <div
                        class="group flex w-full items-center gap-0.5 text-sm transition"
                        :class="{
                            'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400': isSelected(node.path),
                            'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5': !isSelected(node.path),
                        }"
                        :style="'padding-left: ' + (node.depth * 0.75 + 0.5) + 'rem'"
                    >
                        {{-- Expand/collapse toggle --}}
                        <button
                            type="button"
                            x-on:click.stop="toggleFolder(node.path)"
                            class="flex size-5 shrink-0 items-center justify-center rounded text-gray-400 transition hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                        >
                            <template x-if="!isLoading(node.path)">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    class="size-3.5 transition-transform"
                                    :class="{ 'rotate-90': !!expandedFolders[node.path] }"
                                >
                                    <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </template>
                            <template x-if="isLoading(node.path)">
                                <x-filament::loading-indicator class="size-3.5" />
                            </template>
                        </button>

                        {{-- Folder name (selects) --}}
                        <button
                            type="button"
                            x-on:click.stop="selectFolder(node.path)"
                            class="flex min-w-0 flex-1 items-center gap-1.5 truncate py-1 pr-2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="size-4 shrink-0"
                                :class="{
                                    'text-primary-500 dark:text-primary-400': isSelected(node.path),
                                    'text-amber-500 dark:text-amber-400': !isSelected(node.path),
                                }"
                            >
                                <path d="M3.75 3A1.75 1.75 0 0 0 2 4.75v3.26a3.235 3.235 0 0 1 1.75-.51h12.5c.644 0 1.245.188 1.75.51V6.75A1.75 1.75 0 0 0 16.25 5h-4.836a.25.25 0 0 1-.177-.073L9.823 3.513A1.75 1.75 0 0 0 8.586 3H3.75ZM3.75 9A1.75 1.75 0 0 0 2 10.75v4.5c0 .966.784 1.75 1.75 1.75h12.5A1.75 1.75 0 0 0 18 15.25v-4.5A1.75 1.75 0 0 0 16.25 9H3.75Z" />
                            </svg>
                            <span class="truncate" x-text="node.name"></span>
                        </button>
                    </div>

                    {{-- Loading skeleton for this node's children --}}
                    <template x-if="showLoadingSkeleton(node.path)">
                        <div :style="'padding-left: ' + ((node.depth + 1) * 0.75 + 0.5) + 'rem'" class="py-1 pr-2">
                            <div class="h-4 w-24 animate-pulse rounded bg-gray-200 dark:bg-white/10"></div>
                        </div>
                    </template>

                    {{-- Empty state --}}
                    <template x-if="showEmptyState(node.path)">
                        <div :style="'padding-left: ' + ((node.depth + 1) * 0.75 + 0.5) + 'rem'" class="py-1 pr-2">
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">
                                {{ __('filament-file-manager::file-manager.sidebar.empty') }}
                            </span>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- Footer: selected path --}}
        <div class="border-t border-gray-200 px-3 py-2 dark:border-white/10">
            <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                <span x-text="selectedPath === '' ? '/' : '/' + selectedPath"></span>
            </p>
        </div>
    </div>
</x-dynamic-component>
