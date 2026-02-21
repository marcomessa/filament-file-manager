# File Manager

Plugin File Manager per Laravel + Filament v5. Gestione completa di file e cartelle con supporto multi-disco, anteprime, thumbnail e integrazione nei form Filament.

## Funzionalità

- Upload, download, rinomina, sposta ed elimina file e cartelle
- Selezione singola e multipla con shortcut da tastiera (`Ctrl+A`, `Esc`, `Canc`, `F2`)
- Vista a griglia e a lista, ordinamento per nome, dimensione, data e tipo
- Supporto multi-disco (local, public, S3, ecc.)
- Generazione automatica di thumbnail per le immagini
- Anteprima di immagini, video, audio, codice e documenti
- Componente `FilePicker` per i form Filament
- Sanitizzazione dei path e blocco estensioni pericolose
- Cache per i listing dei dischi remoti

## Installazione

### 1. Registrare il plugin nel pannello Filament

In `AdminPanelProvider`:

```php
use MarcoMessa\FileManager\FileManagerPlugin;

$panel->plugins([
    FileManagerPlugin::make(),
]);
```

### 2. Pubblicare la configurazione

```bash
php artisan vendor:publish --tag=file-manager-config
```

### 3. Registrare gli asset CSS

```bash
php artisan filament:assets
```

## Configurazione

Il file `config/file-manager.php` contiene tutte le opzioni:

```php
return [
    // Disco predefinito
    'default_disk' => 'public',

    // Dischi disponibili con etichette
    'disks' => [
        'public' => ['label' => 'Public'],
        'local' => ['label' => 'Private'],
        's3' => ['label' => 'S3 Private'],
        's3Public' => ['label' => 'S3 Public'],
    ],

    // Estensioni bloccate (php, exe, bat, sh, ecc.)
    'denied_extensions' => [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'com', 'cgi', 'pl',
        'msi', 'scr', 'pif', 'vbs', 'vbe', 'js', 'wsh', 'wsf',
    ],

    // Limiti upload
    'max_upload_size' => 50 * 1024,       // KB (50 MB)
    'max_uploads_per_batch' => 20,

    // Cache per dischi remoti (secondi)
    'cache_ttl' => 60,

    // Thumbnail
    'thumbnails' => [
        'enabled' => true,
        'directory' => '.thumbnails',
        'width' => 200,
        'height' => 200,
        'quality' => 80,
    ],

    // URL temporanei per S3 (minuti)
    'temporary_url_expiration' => 5,
];
```

## Plugin Filament

`FileManagerPlugin` supporta le seguenti opzioni di configurazione:

```php
FileManagerPlugin::make()
    ->defaultDisk('public')                        // Disco predefinito
    ->disks(['public', 'local', 's3'])             // Dischi disponibili
    ->navigationGroup('Gestione File')             // Gruppo nel menu
    ->navigationIcon('heroicon-o-folder')          // Icona nel menu
    ->navigationSort(5)                            // Ordinamento nel menu
```

## Componente FilePicker

`FilePicker` è un campo form Filament per selezionare file dal file manager.

### Uso base

```php
use MarcoMessa\FileManager\Forms\Components\FilePicker;

FilePicker::make('document')
    ->label('Documento'),
```

### Selezione multipla

```php
FilePicker::make('attachments')
    ->multiple(),
```

### Filtro per categoria

```php
use MarcoMessa\FileManager\Enums\FileCategory;

FilePicker::make('photo')
    ->acceptedCategories([FileCategory::Image]),

FilePicker::make('media')
    ->acceptedCategories([FileCategory::Image, FileCategory::Video]),
```

Categorie disponibili: `Image`, `Document`, `Audio`, `Video`, `Archive`, `Code`, `Other`.

### Anteprima immagini

L'anteprima si attiva automaticamente quando l'unica categoria accettata è `Image`. Si può forzare manualmente:

```php
FilePicker::make('cover')
    ->imagePreview(),
```

### Disco specifico

```php
FilePicker::make('private_doc')
    ->disk('local'),
```

## Pagina File Manager

La pagina admin è registrata automaticamente dal plugin e accessibile dal menu di navigazione. Include tutte le funzionalità: navigazione cartelle, breadcrumb, upload, operazioni bulk, anteprima e cambio disco.

## Servizi

### FileManagerService

Servizio principale per tutte le operazioni su file e cartelle:

```php
use MarcoMessa\FileManager\Services\FileManagerService;

$service = app(FileManagerService::class);

// Elenco contenuti directory
$listing = $service->listDirectory('public', 'uploads');
$listing->folders; // array<FolderItem>
$listing->files;   // array<FileItem>

// Upload
$path = $service->upload('public', 'uploads', $uploadedFile);

// Operazioni
$service->rename('public', 'uploads/old.pdf', 'new.pdf');
$service->move('public', 'uploads/file.pdf', 'archive');
$service->delete('public', 'uploads/file.pdf');
$service->createFolder('public', '', 'nuova-cartella');

// Operazioni bulk
$service->deleteBulk('public', ['file1.pdf', 'file2.pdf']);
$service->moveBulk('public', ['file1.pdf', 'file2.pdf'], 'archive');

// Download
return $service->download('public', 'uploads/file.pdf');
```

### ThumbnailService

Genera e gestisce thumbnail per le immagini (jpg, jpeg, png, gif, webp, avif, bmp):

```php
use MarcoMessa\FileManager\Services\ThumbnailService;

$service = app(ThumbnailService::class);

$url = $service->getThumbnailUrl('public', 'photo.jpg');       // Genera se mancante
$url = $service->getExistingThumbnailUrl('public', 'photo.jpg'); // Solo se esiste
$service->delete('public', 'photo.jpg');                        // Elimina thumbnail
```

### FileTypeResolver

Mappa le estensioni a categorie, icone e colori:

```php
use MarcoMessa\FileManager\Services\FileTypeResolver;

$resolver = app(FileTypeResolver::class);

$resolver->resolve('photo.jpg');   // FileCategory::Image
$resolver->icon('photo.jpg');      // 'heroicon-o-photo'
$resolver->color('photo.jpg');     // 'text-purple-500'
$resolver->mimeType('jpg');        // 'image/jpeg'
```

## Build CSS

Il package usa Tailwind CSS v4. Per compilare gli stili:

```bash
cd packages/file-manager
npm install
npm run build
```

Dopo la build, registrare gli asset in Filament:

```bash
php artisan filament:assets
```

Il CSS compilato viene generato in `resources/dist/file-manager.css`.

## Testing

Eseguire i test del package:

```bash
php artisan test --compact tests/Feature/FileManagerPageTest.php
php artisan test --compact tests/Feature/FileManagerServiceTest.php
php artisan test --compact tests/Feature/FileOperationsTest.php
php artisan test --compact tests/Feature/FolderOperationsTest.php
php artisan test --compact tests/Feature/SelectionTest.php
php artisan test --compact tests/Feature/FileManagerPickerTest.php
php artisan test --compact tests/Feature/FilePickerFieldTest.php
```

Oppure tutti insieme:

```bash
php artisan test --compact packages/file-manager/tests
```
