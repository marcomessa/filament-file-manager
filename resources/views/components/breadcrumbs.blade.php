<nav class="flex items-center gap-1.5 text-sm">
    @foreach ($this->getBreadcrumbs() as $index => $crumb)
        @if ($index > 0)
            <x-filament::icon icon="heroicon-m-chevron-right" class="size-3.5 shrink-0 text-gray-300 dark:text-gray-600" />
        @endif

        @if ($loop->last && $index > 0)
            <span class="truncate font-medium text-gray-700 dark:text-gray-200">
                {{ $crumb['name'] }}
            </span>
        @else
            <button
                wire:click="navigateTo('{{ $crumb['path'] }}')"
                type="button"
                class="flex shrink-0 items-center gap-1 rounded-md px-1.5 py-0.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200"
            >
                @if ($index === 0)
                    <x-filament::icon icon="heroicon-m-home" class="size-3.5" />
                @endif
                <span class="max-w-[120px] truncate">{{ $crumb['name'] }}</span>
            </button>
        @endif
    @endforeach
</nav>
