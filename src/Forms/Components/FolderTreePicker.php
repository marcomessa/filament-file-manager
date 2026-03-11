<?php

namespace MmesDesign\FilamentFileManager\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Renderless;

class FolderTreePicker extends Field
{
    protected string $view = 'filament-file-manager::forms.components.folder-tree-picker';

    protected string|Closure|null $disk = null;

    public function disk(string|Closure|null $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function getDisk(): string
    {
        return $this->evaluate($this->disk) ?: config('filament-file-manager.disk', 'public');
    }

    /**
     * @return array<int, array{name: string, path: string}>
     */
    #[ExposedLivewireMethod]
    #[Renderless]
    public function getSubfolders(string $path): array
    {
        $directories = Storage::disk($this->getDisk())->directories($path);

        sort($directories);

        return array_map(fn (string $dir): array => [
            'name' => basename($dir),
            'path' => $dir,
        ], $directories);
    }
}
