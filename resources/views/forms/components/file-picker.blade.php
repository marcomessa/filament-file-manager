@php
    $fieldWrapperView = $getFieldWrapperView();
    $id = $getId();
    $isDisabled = $isDisabled();
    $isMultiple = $isMultiple();
    $isImagePreview = $isImagePreview();
    $state = $getState();
    $statePath = $getStatePath();
    $previewItems = $getPreviewItems();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-data
        x-on:file-picker-selected.window="
            if ($event.detail[0]?.fieldId === '{{ $id }}') {
                $wire.set('{{ $statePath }}', $event.detail[0]?.paths);
                $wire.unmountFormComponentAction();
            }
        "
    >
        @if ($previewItems !== [])
            @if ($isImagePreview)
                <div class="flex flex-col items-start gap-1.5">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($previewItems as $item)
                            @php
                                $imgSrc = $item['thumbnailUrl'] ?? $item['fileUrl'];
                            @endphp

                            <div class="size-32 shrink-0 overflow-hidden rounded-lg bg-gray-50 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                                @if ($imgSrc)
                                    <img
                                        src="{{ $imgSrc }}"
                                        alt="{{ $item['name'] }}"
                                        class="size-full object-cover"
                                    />
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <x-filament::icon :icon="$item['icon']" @class(['size-8', $item['iconColor']]) />
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @unless ($isDisabled)
                        {{ $getAction('pick') }}
                    @endunless
                </div>
            @else
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($previewItems as $item)
                        <div class="flex items-center gap-2 rounded-lg bg-gray-50 px-2.5 py-1.5 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                            @if ($item['thumbnailUrl'])
                                <img
                                    src="{{ $item['thumbnailUrl'] }}"
                                    alt="{{ $item['name'] }}"
                                    class="size-8 shrink-0 rounded object-cover"
                                />
                            @else
                                <x-filament::icon :icon="$item['icon']" @class(['size-5 shrink-0', $item['iconColor']]) />
                            @endif
                            <span class="max-w-[12rem] truncate text-sm text-gray-700 dark:text-gray-300" title="{{ $item['name'] }}">
                                {{ $item['name'] }}
                            </span>
                        </div>
                    @endforeach

                    @unless ($isDisabled)
                        <div class="shrink-0">
                            {{ $getAction('pick') }}
                        </div>
                    @endunless
                </div>
            @endif
        @else
            <div class="flex items-center gap-3">
                @if (filled($placeholder = $getPlaceholder()))
                    <span class="min-w-0 flex-1 truncate text-sm text-gray-400 dark:text-gray-500">
                        {{ $placeholder }}
                    </span>
                @endif

                @unless ($isDisabled)
                    <div class="shrink-0">
                        {{ $getAction('pick') }}
                    </div>
                @endunless
            </div>
        @endif
    </div>
</x-dynamic-component>
