<div class="flex items-center justify-center p-4">
    @switch($category)
        @case(\MmesDesign\FilamentFileManager\Enums\FileCategory::Image)
            @if ($url)
                <img
                    src="{{ $url }}"
                    alt="{{ $name }}"
                    class="max-h-[70vh] max-w-full rounded-lg object-contain"
                />
            @else
                @include('filament-file-manager::components.file-preview-info')
            @endif
            @break

        @case(\MmesDesign\FilamentFileManager\Enums\FileCategory::Audio)
            @if ($url)
                <div class="flex w-full flex-col items-center gap-6 py-8">
                    <div class="flex size-20 items-center justify-center rounded-full bg-green-50 dark:bg-green-500/10">
                        <x-filament::icon icon="heroicon-o-musical-note" class="size-10 text-green-500" />
                    </div>
                    <audio controls class="w-full max-w-md" preload="metadata">
                        <source src="{{ $url }}" type="{{ $mimeType }}">
                        {{ __('filament-file-manager::file-manager.misc.audio_not_supported') }}
                    </audio>
                </div>
            @else
                @include('filament-file-manager::components.file-preview-info')
            @endif
            @break

        @case(\MmesDesign\FilamentFileManager\Enums\FileCategory::Video)
            @if ($url)
                <video
                    controls
                    class="max-h-[70vh] max-w-full rounded-lg"
                    preload="metadata"
                >
                    <source src="{{ $url }}" type="{{ $mimeType }}">
                    {{ __('filament-file-manager::file-manager.misc.video_not_supported') }}
                </video>
            @else
                @include('filament-file-manager::components.file-preview-info')
            @endif
            @break

        @case(\MmesDesign\FilamentFileManager\Enums\FileCategory::Document)
            @if ($extension === 'pdf' && $url)
                <iframe
                    src="{{ $url }}"
                    class="h-[70vh] w-full rounded-lg border-0"
                ></iframe>
            @else
                @include('filament-file-manager::components.file-preview-info')
            @endif
            @break

        @case(\MmesDesign\FilamentFileManager\Enums\FileCategory::Code)
            <div class="w-full overflow-auto rounded-lg bg-gray-900 p-4">
                <pre class="text-sm leading-relaxed text-gray-100"><code>{{ $content ?? __('filament-file-manager::file-manager.misc.cannot_read_file') }}</code></pre>
            </div>
            @break

        @default
            @include('filament-file-manager::components.file-preview-info')
    @endswitch
</div>
