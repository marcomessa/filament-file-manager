<?php

namespace MmesDesign\FilamentFileManager\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use MmesDesign\FilamentFileManager\Services\FileTypeResolver;

class FileManagerMarkdownEditor extends MarkdownEditor
{
    protected string|Closure|null $fmDisk = null;

    protected bool|Closure $fmMultiple = true;

    /** @var array<int, FileCategory>|Closure */
    protected array|Closure $fmAcceptedCategories = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->hintAction(
            fn (FileManagerMarkdownEditor $component): Action => $component->getFileManagerAction(),
        );

        $this->extraAlpineAttributes([
            'x-on:fm-insert-markdown.window' => '
                if ($event.detail.fieldKey === $el.id && editor) {
                    editor.codemirror.replaceSelection($event.detail.markdown);
                    editor.codemirror.focus();
                }
            ',
        ]);
    }

    public function getFileManagerAction(): Action
    {
        $disk = $this->getFmDisk() ?: config('filament-file-manager.disk', 'public');
        $multiple = $this->isFmMultiple();
        $acceptedCategories = $this->getFmAcceptedCategoryValues();

        return Action::make('fileManager')
            ->label(__('filament-file-manager::file-manager.editors.file_manager'))
            ->icon(Heroicon::OutlinedFolderOpen)
            ->color('gray')
            ->link()
            ->slideOver()
            ->modalHeading(__('filament-file-manager::file-manager.editors.select_files'))
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalContent(fn (): View => view(
                'filament-file-manager::markdown-editor.file-picker-modal',
                [
                    'fieldId' => 'md-editor-fm-' . $this->getKey(),
                    'multiple' => $multiple,
                    'disk' => $disk,
                    'acceptedCategories' => $acceptedCategories,
                ],
            ))
            ->action(function (array $arguments, Component $livewire) use ($disk): void {
                $paths = json_decode($arguments['selectedPaths'] ?? '[]', true);

                if (! is_array($paths)) {
                    $paths = [$paths];
                }

                $paths = array_values(array_filter($paths));

                if ($paths === []) {
                    return;
                }

                $fileManagerService = app(FileManagerService::class);
                $fileTypeResolver = app(FileTypeResolver::class);
                $skippedCount = 0;

                $markdown = collect($paths)->map(function (string $path) use ($fileManagerService, $fileTypeResolver, $disk, &$skippedCount): ?string {
                    $url = $fileManagerService->getUrl($disk, $path);

                    if ($url === null) {
                        $skippedCount++;

                        return null;
                    }

                    $name = basename($path);
                    $category = $fileTypeResolver->resolve($name);

                    return $category === FileCategory::Image
                        ? "![{$name}]({$url})"
                        : "[{$name}]({$url})";
                })->filter()->implode("\n");

                if ($markdown !== '') {
                    $livewire->dispatch(
                        'fm-insert-markdown',
                        fieldKey: $this->getId(),
                        markdown: $markdown,
                    );
                }

                if ($skippedCount > 0) {
                    Notification::make()
                        ->warning()
                        ->title(__('filament-file-manager::file-manager.editors.no_url_warning'))
                        ->send();
                }
            });
    }

    public function fmDisk(string|Closure|null $disk): static
    {
        $this->fmDisk = $disk;

        return $this;
    }

    public function getFmDisk(): ?string
    {
        return $this->evaluate($this->fmDisk);
    }

    public function fmMultiple(bool|Closure $condition = true): static
    {
        $this->fmMultiple = $condition;

        return $this;
    }

    public function isFmMultiple(): bool
    {
        return (bool) $this->evaluate($this->fmMultiple);
    }

    /**
     * @param  array<int, FileCategory>|Closure  $categories
     */
    public function fmAcceptedCategories(array|Closure $categories): static
    {
        $this->fmAcceptedCategories = $categories;

        return $this;
    }

    /**
     * @return array<int, FileCategory>
     */
    public function getFmAcceptedCategories(): array
    {
        return $this->evaluate($this->fmAcceptedCategories);
    }

    /**
     * @return array<int, string>
     */
    public function getFmAcceptedCategoryValues(): array
    {
        return array_map(
            fn (FileCategory $category): string => $category->value,
            $this->getFmAcceptedCategories(),
        );
    }
}
