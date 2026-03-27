<div
    class="h-[70vh]"
    x-data="{ fieldId: @js($fieldId) }"
    x-on:file-picker-selected.window="
        if ($event.detail.fieldId === fieldId) {
            const paths = $event.detail.paths ?? [];
            $wire.callMountedAction({ selectedPaths: JSON.stringify(Array.isArray(paths) ? paths : [paths]) });
        }
    "
>
    <livewire:filament-file-manager-picker
        :multiple="$multiple"
        :disk="$disk"
        :field-id="$fieldId"
        :accepted-categories="$acceptedCategories"
        :selected-paths="[]"
    />
</div>
