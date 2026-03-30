# Filament File Manager

A powerful file manager plugin for [Filament](https://filamentphp.com). Browse, upload, rename, move, and delete files directly from your admin panel.

![Filament File Manager](https://raw.githubusercontent.com/marcomessa/filament-file-manager/main/art/screenshot.png)

## Features

- File Manager page with grid and list views
- Sorting by name, size, date, and type
- Drag-and-drop file upload with size and batch limits
- Create, rename, move, and delete files and folders
- Bulk operations (delete, move) with multi-select
- Breadcrumb navigation
- Keyboard shortcuts (Ctrl+A, Delete, F2, Escape)
- File preview for images, video, audio, code, and documents
- Automatic thumbnail generation for images
- `FilePicker` form component for selecting files in your resources
- `RichEditor` and `MarkdownEditor` integration
- Granular permissions (access, upload, download, delete, rename, move, create folder)
- Dark mode support
- Translations (English, Italian)
- Security: path sanitization and blocked dangerous extensions

## Requirements

- PHP 8.2+
- Laravel 11+
- Filament 5+

## Installation

```bash
composer require mmes-design/filament-file-manager
```

Register the plugin in your panel provider:

```php
use MmesDesign\FilamentFileManager\FileManagerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FileManagerPlugin::make(),
        ]);
}
```

## Documentation

Full documentation is available at **[docs.mmes.dev/filament-file-manager](https://docs.mmes.dev/filament-file-manager)**.

## PRO Version

Need remote disks (S3, GCS, FTP/SFTP), multi-disk switching, Spatie Media Library integration, and more? Check out **[Filament File Manager PRO](https://docs.mmes.dev/filament-file-manager/upgrading-to-pro)**.

## License

The MIT License (MIT). Please see [License File](https://github.com/marcomessa/filament-file-manager/blob/main/LICENSE.md) for more information.
