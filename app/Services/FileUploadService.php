<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class FileUploadService
{
    /**
     * Check if there is enough disk space for upload
     *
     * @param int $requiredBytes
     * @param string $disk
     * @return bool
     */
    public function hasSufficientDiskSpace(int $requiredBytes, string $disk = 'public'): bool
    {
        try {
            $path = Storage::disk($disk)->path('');
            $freeSpace = disk_free_space($path);

            // Ensure at least 10% free space or 1GB remains after upload
            $minFreeSpace = max(
                $freeSpace * 0.1,
                1024 * 1024 * 1024 // 1GB
            );

            return ($freeSpace - $requiredBytes) > $minFreeSpace;
        } catch (Exception $e) {
            Log::error('Failed to check disk space', [
                'error' => $e->getMessage(),
                'disk' => $disk,
            ]);

            // Default to false if we can't check
            return false;
        }
    }

    /**
     * Get current storage statistics
     *
     * @param string $disk
     * @return array
     */
    public function getStorageStats(string $disk = 'public'): array
    {
        try {
            $path = Storage::disk($disk)->path('');
            $totalSpace = disk_total_space($path);
            $freeSpace = disk_free_space($path);
            $usedSpace = $totalSpace - $freeSpace;

            return [
                'total' => $totalSpace,
                'free' => $freeSpace,
                'used' => $usedSpace,
                'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2),
                'formatted' => [
                    'total' => $this->formatBytes($totalSpace),
                    'free' => $this->formatBytes($freeSpace),
                    'used' => $this->formatBytes($usedSpace),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Failed to get storage stats', [
                'error' => $e->getMessage(),
                'disk' => $disk,
            ]);

            return [];
        }
    }

    /**
     * Sanitize filename to prevent path traversal and other attacks
     *
     * @param string $filename
     * @return string
     */
    public function sanitizeFilename(string $filename): string
    {
        // Get the file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        // Remove path traversal attempts
        $basename = str_replace(['..', '/', '\\', "\0"], '', $basename);

        // Remove special characters but keep some safe ones
        $basename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $basename);

        // Limit length
        $basename = substr($basename, 0, 100);

        // Ensure it's not empty
        if (empty($basename)) {
            $basename = 'file';
        }

        return $basename . '.' . $extension;
    }

    /**
     * Generate unique filename with collision prevention
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $disk
     * @return string
     */
    public function generateUniqueFilename(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $sanitizedName = $this->sanitizeFilename($file->getClientOriginalName());
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($sanitizedName, PATHINFO_FILENAME);

        // Generate hash from file content for deduplication
        $hash = substr(md5_file($file->getRealPath()), 0, 8);

        // Create unique name with timestamp, hash, and random string
        $timestamp = time();
        $random = Str::random(8);

        $filename = sprintf(
            '%s_%s_%s_%s.%s',
            $basename,
            $timestamp,
            $hash,
            $random,
            $extension
        );

        // Ensure uniqueness
        $counter = 1;
        $originalFilename = $filename;

        while (Storage::disk($disk)->exists($directory . '/' . $filename)) {
            $filename = sprintf(
                '%s_%s_%s_%s_%d.%s',
                $basename,
                $timestamp,
                $hash,
                $random,
                $counter,
                $extension
            );
            $counter++;

            // Prevent infinite loop
            if ($counter > 1000) {
                throw new Exception('Unable to generate unique filename after 1000 attempts');
            }
        }

        return $filename;
    }

    /**
     * Validate file for security threats
     *
     * @param UploadedFile $file
     * @return array
     */
    public function validateFileSecure(UploadedFile $file): array
    {
        $errors = [];

        // Check for double extensions (exploit.php.jpg)
        $filename = $file->getClientOriginalName();
        if (substr_count($filename, '.') > 1) {
            $errors[] = 'Multiple file extensions are not allowed';
        }

        // Validate MIME type against extension
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        $allowedMimeTypes = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
        ];

        if (!isset($allowedMimeTypes[$extension]) || !in_array($mimeType, $allowedMimeTypes[$extension])) {
            $errors[] = 'File MIME type does not match extension';
        }

        // Check for PHP code in file content
        $content = file_get_contents($file->getRealPath(), false, null, 0, 8192);
        if (stripos($content, '<?php') !== false || stripos($content, '<?=') !== false) {
            $errors[] = 'File contains potentially malicious code';
        }

        // Verify it's actually an image
        if (!getimagesize($file->getRealPath())) {
            $errors[] = 'File is not a valid image';
        }

        return $errors;
    }

    /**
     * Scan file for viruses (requires ClamAV or similar)
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function scanForViruses(UploadedFile $file): bool
    {
        // If virus scanning is disabled in config, skip
        if (!config('filesystems.virus_scanning.enabled', false)) {
            return true;
        }

        try {
            $scanMethod = config('filesystems.virus_scanning.method', 'clamav');

            if ($scanMethod === 'clamav') {
                return $this->scanWithClamAV($file);
            }

            // Add other scanning methods here

            return true;
        } catch (Exception $e) {
            Log::error('Virus scan failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            // Fail closed: reject file if scan fails
            return false;
        }
    }

    /**
     * Scan file with ClamAV
     *
     * @param UploadedFile $file
     * @return bool
     */
    protected function scanWithClamAV(UploadedFile $file): bool
    {
        $clamavSocket = config('filesystems.virus_scanning.clamav_socket', '/var/run/clamav/clamd.ctl');

        // Check if ClamAV is available
        if (!file_exists($clamavSocket)) {
            Log::warning('ClamAV socket not found, skipping virus scan', [
                'socket' => $clamavSocket,
            ]);

            // If strict mode, fail; otherwise allow
            if (config('filesystems.virus_scanning.strict', false)) {
                return false;
            }

            return true;
        }

        // Scan using clamscan command
        $filePath = $file->getRealPath();
        $output = [];
        $returnCode = 0;

        exec("clamscan --no-summary " . escapeshellarg($filePath), $output, $returnCode);

        // Return code 0 = clean, 1 = infected, 2 = error
        if ($returnCode === 1) {
            Log::warning('Virus detected in uploaded file', [
                'file' => $file->getClientOriginalName(),
                'scan_result' => implode("\n", $output),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Upload file with all security checks
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $disk
     * @return array
     */
    public function uploadSecurely(UploadedFile $file, string $directory, string $disk = 'public'): array
    {
        // 1. Check disk space
        if (!$this->hasSufficientDiskSpace($file->getSize(), $disk)) {
            throw new Exception('Insufficient disk space for upload');
        }

        // 2. Validate file security
        $securityErrors = $this->validateFileSecure($file);
        if (!empty($securityErrors)) {
            throw new Exception('File validation failed: ' . implode(', ', $securityErrors));
        }

        // 3. Scan for viruses
        if (!$this->scanForViruses($file)) {
            throw new Exception('File failed virus scan');
        }

        // 4. Generate unique filename
        $filename = $this->generateUniqueFilename($file, $directory, $disk);

        // 5. Store with proper permissions
        $filePath = $directory . '/' . $filename;

        try {
            Storage::disk($disk)->putFileAs($directory, $file, $filename);

            // Verify the file was actually written
            if (!Storage::disk($disk)->exists($filePath)) {
                throw new Exception('File upload verification failed');
            }

            // Set proper permissions (readable by web server)
            $fullPath = Storage::disk($disk)->path($filePath);
            chmod($fullPath, 0644);

            return [
                'filename' => $filename,
                'filepath' => $filePath,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
            ];

        } catch (Exception $e) {
            // Clean up if upload failed
            if (Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->delete($filePath);
            }

            throw new Exception('File upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human-readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Find orphaned files (files in storage without DB records)
     *
     * @param string $directory
     * @param string $disk
     * @param string $model
     * @return array
     */
    public function findOrphanedFiles(string $directory, string $disk = 'public', string $model = null): array
    {
        $orphaned = [];

        try {
            // Get all files in directory
            $files = Storage::disk($disk)->allFiles($directory);

            if ($model) {
                // Get all file paths from database
                $dbFiles = $model::pluck('file_path')->toArray();

                foreach ($files as $file) {
                    if (!in_array($file, $dbFiles)) {
                        $orphaned[] = [
                            'path' => $file,
                            'size' => Storage::disk($disk)->size($file),
                            'modified' => Storage::disk($disk)->lastModified($file),
                        ];
                    }
                }
            }

        } catch (Exception $e) {
            Log::error('Failed to find orphaned files', [
                'directory' => $directory,
                'error' => $e->getMessage(),
            ]);
        }

        return $orphaned;
    }

    /**
     * Clean up orphaned files
     *
     * @param array $orphanedFiles
     * @param string $disk
     * @return int Number of files deleted
     */
    public function cleanupOrphanedFiles(array $orphanedFiles, string $disk = 'public'): int
    {
        $deleted = 0;

        foreach ($orphanedFiles as $fileInfo) {
            try {
                if (Storage::disk($disk)->exists($fileInfo['path'])) {
                    Storage::disk($disk)->delete($fileInfo['path']);
                    $deleted++;

                    Log::info('Deleted orphaned file', [
                        'path' => $fileInfo['path'],
                        'size' => $fileInfo['size'],
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Failed to delete orphaned file', [
                    'path' => $fileInfo['path'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $deleted;
    }
}
