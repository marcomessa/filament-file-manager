<?php

namespace MmesDesign\FilamentFileManager\Support;

class PathSanitizer
{
    /**
     * Sanitize a path to prevent directory traversal attacks.
     *
     * @throws \InvalidArgumentException
     */
    public function sanitize(string $path): string
    {
        $path = str_replace("\0", '', $path);

        if (str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            throw new \InvalidArgumentException('Absolute paths are not allowed.');
        }

        $path = str_replace('\\', '/', $path);

        $segments = explode('/', $path);

        foreach ($segments as $segment) {
            if ($segment === '..') {
                throw new \InvalidArgumentException('Directory traversal is not allowed.');
            }
        }

        $path = implode('/', array_filter($segments, fn (string $s): bool => $s !== '' && $s !== '.'));

        return $path;
    }

    /**
     * Sanitize and join a directory path with a filename.
     *
     * @throws \InvalidArgumentException
     */
    public function join(string $directory, string $name): string
    {
        $directory = $this->sanitize($directory);
        $name = $this->sanitize($name);

        if (str_contains($name, '/')) {
            throw new \InvalidArgumentException('Filename cannot contain path separators.');
        }

        if ($directory === '') {
            return $name;
        }

        return $directory.'/'.$name;
    }

    /**
     * Check if a file extension is in the deny list.
     */
    public function isExtensionDenied(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, config('filament-file-manager.denied_extensions', []), true);
    }
}
