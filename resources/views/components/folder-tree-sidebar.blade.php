<nav
    class="flex h-full w-56 shrink-0 flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
    aria-label="{{ __('filament-file-manager::file-manager.sidebar.folders') }}"
>
    {{-- Header --}}
    <div class="border-b border-gray-200 px-3 py-2.5 dark:border-white/10">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
            {{ __('filament-file-manager::file-manager.sidebar.folders') }}
        </h3>
    </div>

    {{-- Tree --}}
    <div class="flex-1 overflow-y-auto py-1" role="tree" aria-label="{{ __('filament-file-manager::file-manager.sidebar.folders') }}">
        {{-- Root node --}}
        <div role="treeitem" aria-selected="{{ $currentPath === '' ? 'true' : 'false' }}" aria-level="1">
            <button
                wire:click="navigateViaTree('')"
                type="button"
                @class([
                    'flex w-full items-center gap-2 px-3 py-1.5 text-sm transition hover:bg-gray-50 dark:hover:bg-white/5',
                    'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' => $currentPath === '',
                    'text-gray-700 dark:text-gray-300' => $currentPath !== '',
                ])
            >
                <x-filament::icon icon="heroicon-m-home" class="size-4 shrink-0 text-gray-400 dark:text-gray-500" />
                <span class="truncate font-medium">{{ $this->getDiskLabel() }}</span>
            </button>
        </div>

        {{-- Root children --}}
        @if (isset($treeNodes['']))
            <div role="group">
                @foreach ($treeNodes[''] as $node)
                    @include('filament-file-manager::components.folder-tree-node', [
                        'node' => $node,
                        'depth' => 1,
                    ])
                @endforeach
            </div>
        @endif
    </div>
</nav>
