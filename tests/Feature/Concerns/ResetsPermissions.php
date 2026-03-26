<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature\Concerns;

use MmesDesign\FilamentFileManager\FileManagerPlugin;

trait ResetsPermissions
{
    protected function resetPermissions(): void
    {
        $plugin = FileManagerPlugin::get();
        $plugin->canAccess(true);
        $plugin->canUpload(true);
        $plugin->canDelete(true);
        $plugin->canRename(true);
        $plugin->canMove(true);
        $plugin->canDownload(true);
        $plugin->canCreateFolder(true);
    }
}
