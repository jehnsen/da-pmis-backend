<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security settings for file uploads including virus scanning,
    | storage limits, and file validation rules.
    |
    */

    'upload_security' => [
        // Maximum file size in bytes (default: 5MB)
        'max_file_size' => env('UPLOAD_MAX_FILE_SIZE', 5 * 1024 * 1024),

        // Minimum free disk space to maintain (in bytes, default: 1GB)
        'min_free_space' => env('UPLOAD_MIN_FREE_SPACE', 1024 * 1024 * 1024),

        // Minimum free disk space percentage (0-100)
        'min_free_space_percentage' => env('UPLOAD_MIN_FREE_SPACE_PCT', 10),

        // Allowed file extensions
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],

        // Allowed MIME types
        'allowed_mime_types' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
        ],

        // Enable strict validation (MIME type must match extension)
        'strict_validation' => env('UPLOAD_STRICT_VALIDATION', true),

        // Maximum filename length
        'max_filename_length' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Virus Scanning Configuration
    |--------------------------------------------------------------------------
    |
    | Configure virus scanning for uploaded files. Requires ClamAV or similar
    | antivirus software installed on the server.
    |
    */

    'virus_scanning' => [
        // Enable virus scanning
        'enabled' => env('VIRUS_SCAN_ENABLED', false),

        // Scanning method: 'clamav', 'disabled'
        'method' => env('VIRUS_SCAN_METHOD', 'clamav'),

        // ClamAV socket path (Unix socket)
        'clamav_socket' => env('CLAMAV_SOCKET', '/var/run/clamav/clamd.ctl'),

        // Strict mode: reject uploads if scanner is unavailable
        'strict' => env('VIRUS_SCAN_STRICT', false),

        // Timeout for virus scan (seconds)
        'timeout' => env('VIRUS_SCAN_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Orphaned File Cleanup Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automatic cleanup of orphaned files (files in storage without
    | corresponding database records).
    |
    */

    'orphaned_cleanup' => [
        // Enable automatic cleanup
        'enabled' => env('ORPHANED_CLEANUP_ENABLED', true),

        // Age threshold in days (only delete files older than this)
        'age_threshold_days' => env('ORPHANED_CLEANUP_AGE_DAYS', 7),

        // Run cleanup automatically via scheduler
        'auto_schedule' => env('ORPHANED_CLEANUP_AUTO_SCHEDULE', false),

        // Schedule frequency: 'daily', 'weekly', 'monthly'
        'schedule_frequency' => env('ORPHANED_CLEANUP_FREQUENCY', 'weekly'),
    ],

];
