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
        x-data="{
            removeItem(path) {
                @if ($isMultiple)
                    let current = $wire.get('{{ $statePath }}') || [];
                    $wire.set('{{ $statePath }}', current.filter(p => p !== path));
                @else
                    $wire.set('{{ $statePath }}', null);
                @endif
            },
            clearAll() {
                $wire.set('{{ $statePath }}', {{ $isMultiple ? '[]' : 'null' }});
            },
        }"
        x-on:file-picker-selected.window="
            if ($event.detail.fieldId === '{{ $id }}') {
                $wire.set('{{ $statePath }}', $event.detail.paths);
                $wire.unmountAction();
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

                            <div class="group relative size-32 shrink-0 overflow-hidden rounded-lg bg-gray-50 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
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

                                @unless ($isDisabled)
                                    <button
                                        type="button"
                                        x-on:click="removeItem(@js($item['path']))"
                                        class="absolute top-1 right-1 flex size-6 items-center justify-center rounded-full bg-gray-900/60 text-white opacity-0 transition hover:bg-danger-600 group-hover:opacity-100"
                                    >
                                        <x-filament::icon icon="heroicon-m-x-mark" class="size-3.5" />
                                    </button>
                                @endunless
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

                            @unless ($isDisabled)
                                <button
                                    type="button"
                                    x-on:click="removeItem(@js($item['path']))"
                                    class="-mr-1 flex size-5 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-200 hover:text-danger-600 dark:hover:bg-white/10 dark:hover:text-danger-400"
                                >
                                    <x-filament::icon icon="heroicon-m-x-mark" class="size-3.5" />
                                </button>
                            @endunless
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
