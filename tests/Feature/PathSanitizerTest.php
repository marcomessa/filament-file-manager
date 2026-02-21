<?php

namespace MmesDesign\FilamentFileManager\Tests\Feature;

use MmesDesign\FilamentFileManager\Support\PathSanitizer;
use Tests\TestCase;

class PathSanitizerTest extends TestCase
{
    public function test_is_extension_denied_blocks_php(): void
    {
        $sanitizer = new PathSanitizer;

        $this->assertTrue($sanitizer->isExtensionDenied('shell.php'));
        $this->assertTrue($sanitizer->isExtensionDenied('SCRIPT.PHP'));
        $this->assertTrue($sanitizer->isExtensionDenied('hack.phtml'));
    }

    public function test_is_extension_denied_allows_safe_files(): void
    {
        $sanitizer = new PathSanitizer;

        $this->assertFalse($sanitizer->isExtensionDenied('image.jpg'));
        $this->assertFalse($sanitizer->isExtensionDenied('document.pdf'));
        $this->assertFalse($sanitizer->isExtensionDenied('data.csv'));
    }
}
