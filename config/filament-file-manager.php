<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk used by the file manager.
    | Must be a local disk (e.g. 'public', 'local').
    | Remote disk support (S3, GCS) is available in the Pro package.
    |
    */

    'disk' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Denied Extensions
    |--------------------------------------------------------------------------
    |
    | File extensions that are always blocked from upload, regardless of
    | other settings. These are dangerous executable file types.
    |
    */

    'denied_extensions' => [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'com', 'cgi', 'pl',
        'msi', 'scr', 'pif', 'vbs', 'vbe', 'js', 'wsh', 'wsf',
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Limits
    |--------------------------------------------------------------------------
    */

    'max_upload_size' => 50 * 1024, // KB (50 MB)

    'max_uploads_per_batch' => 20,

    /*
    |--------------------------------------------------------------------------
    | Thumbnails
    |--------------------------------------------------------------------------
    */

    'thumbnails' => [
        'enabled' => true,
        'directory' => '.thumbnails',
        'width' => 200,
        'height' => 200,
        'quality' => 80,
    ],

];
