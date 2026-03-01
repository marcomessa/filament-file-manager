@php
    $isExpanded = in_array($node['path'], $expandedFolders, true);
    $isActive = $currentPath === $node['path'];
    $hasLoadedChildren = array_key_exists($node['path'], $treeNodes);
    $children = $treeNodes[$node['path']] ?? [];
    $ariaLevel = $depth + 1;
@endphp

<div
    wire:key="tree-{{ $node['path'] }}"
    role="treeitem"
    aria-expanded="{{ $isExpanded ? 'true' : 'false' }}"
    aria-selected="{{ $isActive ? 'true' : 'false' }}"
    aria-level="{{ $ariaLevel }}"
    aria-label="{{ $node['name'] }}"
>
    <div
        @class([
            'group flex w-full items-center gap-0.5 py-1 pr-2 text-sm transition hover:bg-gray-50 dark:hover:bg-white/5',
            'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' => $isActive,
            'text-gray-700 dark:text-gray-300' => ! $isActive,
        ])
        style="padding-left: {{ $depth * 0.75 + 0.5 }}rem"
    >
        {{-- Expand/collapse toggle --}}
        <button
            wire:click="toggleTreeFolder('{{ $node['path'] }}')"
            type="button"
            class="flex size-5 shrink-0 items-center justify-center rounded text-gray-400 transition hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
            aria-label="{{ $isExpanded ? __('filament-file-manager::file-manager.sidebar.collapse') : __('filament-file-manager::file-manager.sidebar.expand') }} {{ $node['name'] }}"
        >
            <x-filament::icon
                :icon="$isExpanded ? 'heroicon-m-chevron-down' : 'heroicon-m-chevron-right'"
                class="size-3.5"
            />
        </button>

        {{-- Folder name (navigates) --}}
        <button
            wire:click="navigateViaTree('{{ $node['path'] }}')"
            type="button"
            class="flex min-w-0 flex-1 items-center gap-1.5 truncate"
        >
            <x-filament::icon
                icon="heroicon-m-folder"
                @class([
                    'size-4 shrink-0',
                    'text-primary-500 dark:text-primary-400' => $isActive,
                    'text-amber-500 dark:text-amber-400' => ! $isActive,
                ])
            />
            <span class="truncate">{{ $node['name'] }}</span>
        </button>
    </div>

    {{-- Children --}}
    @if ($isExpanded)
        <div role="group">
            @if (! $hasLoadedChildren)
                {{-- Loading skeleton --}}
                <div style="padding-left: {{ ($depth + 1) * 0.75 + 0.5 }}rem" class="py-1 pr-2">
                    <div class="h-4 w-24 animate-pulse rounded bg-gray-200 dark:bg-white/10"></div>
                </div>
            @elseif (count($children) === 0)
                {{-- Empty --}}
                <div style="padding-left: {{ ($depth + 1) * 0.75 + 0.5 }}rem" class="py-1 pr-2">
                    <span class="text-xs italic text-gray-400 dark:text-gray-500">
                        {{ __('filament-file-manager::file-manager.sidebar.empty') }}
                    </span>
                </div>
            @else
                @foreach ($children as $childNode)
                    @include('filament-file-manager::components.folder-tree-node', [
                        'node' => $childNode,
                        'depth' => $depth + 1,
                    ])
                @endforeach
            @endif
        </div>
    @endif
</div>
