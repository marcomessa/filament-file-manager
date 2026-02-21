<div class="flex w-full flex-col items-center gap-4 py-8">
    <div class="flex size-20 items-center justify-center rounded-full bg-gray-100 dark:bg-white/5">
        <x-filament::icon :icon="$category->icon()" @class(['size-10', $category->color()]) />
    </div>
    <div class="text-center">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $name }}</p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ strtoupper($extension) }} &middot; {{ $mimeType }}
        </p>
    </div>
    @if ($url)
        <a
            href="{{ $url }}"
            download="{{ $name }}"
            class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary-500"
        >
            <x-filament::icon icon="heroicon-m-arrow-down-tray" class="size-4" />
            {{ __('filament-file-manager::file-manager.actions.download') }}
        </a>
    @endif
</div>
