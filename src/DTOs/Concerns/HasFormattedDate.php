<?php

namespace MmesDesign\FilamentFileManager\DTOs\Concerns;

trait HasFormattedDate
{
    public function formattedDate(): string
    {
        return date('d M Y H:i', $this->lastModified);
    }
}
