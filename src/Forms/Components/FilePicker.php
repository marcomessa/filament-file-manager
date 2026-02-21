<?php

namespace MmesDesign\FilamentFileManager\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use MmesDesign\FilamentFileManager\DTOs\FileItem;
use MmesDesign\FilamentFileManager\Enums\FileCategory;
use MmesDesign\FilamentFileManager\Services\FileManagerService;
use MmesDesign\FilamentFileManager\Services\FileTypeResolver;
use MmesDesign\FilamentFileManager\Services\ThumbnailService;

class FilePicker extends Field
{
    use HasPlaceholder;

    protected string $view = 'filament-file-manager::forms.components.file-picker';

    protected bool|Closure $isMultiple = false;

    protected string|Closure|null $disk = null;

    protected bool|Closure $isImagePreview = false;

    /** @var array<int, FileCategory> | Closure */
    protected array|Closure $acceptedCategories = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->placeholder(__('filament-file-manager::file-manager.labels.no_file_selected'));

        $this->registerActions([
            fn (FilePicker $component): Action => $component->getPickerAction(),
        ]);
    }

    public function getPickerAction(): Action
    {
        return Action::make('pick')
            ->label(__('filament-file-manager::file-manager.actions.browse'))
            ->icon(Heroicon::OutlinedFolder)
            ->color('gray')
            ->link()
            ->slideOver()
            ->modalHeading(__('filament-file-manager::file-manager.actions.select_file'))
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalContent(fn (): View => view('filament-file-manager::forms.components.file-picker-modal', [
                'multiple' => $this->isMultiple(),
                'disk' => $this->getDisk(),
                'fieldId' => $this->getId(),
                'acceptedCategories' => $this->getAcceptedCategoryValues(),
                'selectedPaths' => $this->getState(),
            ]));
    }

    public function multiple(bool|Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }

    public function disk(string|Closure|null $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function getDisk(): ?string
    {
        return $this->evaluate($this->disk);
    }

    /**
     * @param  array<int, FileCategory> | Closure  $categories
     */
    public function acceptedCategories(array|Closure $categories): static
    {
        $this->acceptedCategories = $categories;

        return $this;
    }

    /**
     * @return array<int, FileCategory>
     */
    public function getAcceptedCategories(): array
    {
        return $this->evaluate($this->acceptedCategories);
    }

    public function imagePreview(bool|Closure $condition = true): static
    {
        $this->isImagePreview = $condition;

        return $this;
    }

    public function isImagePreview(): bool
    {
        if ($this->evaluate($this->isImagePreview)) {
            return true;
        }

        $categories = $this->getAcceptedCategories();

        return $categories !== [] && $categories === [FileCategory::Image];
    }

    /**
     * @return array<int, string>
     */
    public function getAcceptedCategoryValues(): array
    {
        return array_map(
            fn (FileCategory $category): string => $category->value,
            $this->getAcceptedCategories(),
        );
    }

    /**
     * @return array<int, array{path: string, name: string, thumbnailUrl: string|null, fileUrl: string|null, icon: string, iconColor: string}>
     */
    public function getPreviewItems(): array
    {
        $state = $this->getState();

        if (blank($state)) {
            return [];
        }

        $paths = is_array($state) ? $state : [$state];
        $paths = array_values(array_filter($paths));

        if ($paths === []) {
            return [];
        }

        $disk = $this->getDisk() ?: config('filament-file-manager.disk', 'public');
        $resolver = app(FileTypeResolver::class);
        $thumbnailService = app(ThumbnailService::class);
        $fileManagerService = app(FileManagerService::class);

        $items = [];

        foreach ($paths as $path) {
            $name = basename($path);
            $category = $resolver->resolve($name);
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $isThumbnailable = in_array($extension, FileItem::THUMBNAILABLE_EXTENSIONS, true);

            $items[] = [
                'path' => $path,
                'name' => $name,
                'thumbnailUrl' => $isThumbnailable ? $thumbnailService->getExistingThumbnailUrl($disk, $path) : null,
                'fileUrl' => $fileManagerService->getUrl($disk, $path),
                'icon' => $category->icon(),
                'iconColor' => $category->color(),
            ];
        }

        return $items;
    }
}
