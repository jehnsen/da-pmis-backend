<?php

namespace App\Console\Commands;

use App\Models\ProjectImage;
use App\Services\FileUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:cleanup-orphaned
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--age-days= : Only delete files older than specified days (default: from config)}
                            {--directory= : Specific directory to clean (default: all project directories)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned files (files in storage without database records)';

    /**
     * File upload service instance
     *
     * @var FileUploadService
     */
    protected $fileUploadService;

    /**
     * Create a new command instance.
     */
    public function __construct(FileUploadService $fileUploadService)
    {
        parent::__construct();
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $ageDays = $this->option('age-days') ?? config('filesystems.orphaned_cleanup.age_threshold_days', 7);
        $directory = $this->option('directory');

        $this->info('Starting orphaned file cleanup...');
        $this->info('Mode: ' . ($isDryRun ? 'DRY RUN (no files will be deleted)' : 'LIVE'));
        $this->info('Age threshold: ' . $ageDays . ' days');

        $totalOrphaned = 0;
        $totalDeleted = 0;
        $totalSize = 0;

        try {
            // Get directories to scan
            $directories = $directory ? [$directory] : $this->getProjectDirectories();

            $this->info('Scanning ' . count($directories) . ' directories...');

            foreach ($directories as $dir) {
                $this->line('');
                $this->line('Scanning: ' . $dir);

                // Find orphaned files
                $orphanedFiles = $this->findOrphanedFilesInDirectory($dir, $ageDays);

                if (empty($orphanedFiles)) {
                    $this->info('  No orphaned files found');
                    continue;
                }

                $totalOrphaned += count($orphanedFiles);

                $this->warn('  Found ' . count($orphanedFiles) . ' orphaned file(s)');

                // Display files
                foreach ($orphanedFiles as $fileInfo) {
                    $age = round((time() - $fileInfo['modified']) / 86400, 1);
                    $size = $this->formatBytes($fileInfo['size']);
                    $totalSize += $fileInfo['size'];

                    $this->line(sprintf(
                        '    - %s (%s, %s days old)',
                        basename($fileInfo['path']),
                        $size,
                        $age
                    ));
                }

                // Delete files if not dry run
                if (!$isDryRun) {
                    $deleted = $this->fileUploadService->cleanupOrphanedFiles($orphanedFiles);
                    $totalDeleted += $deleted;
                    $this->info('  Deleted ' . $deleted . ' file(s)');
                }
            }

            // Summary
            $this->line('');
            $this->line('=================================================');
            $this->info('Cleanup Summary:');
            $this->line('=================================================');
            $this->line('Total orphaned files found: ' . $totalOrphaned);
            $this->line('Total size: ' . $this->formatBytes($totalSize));

            if (!$isDryRun) {
                $this->line('Total files deleted: ' . $totalDeleted);

                Log::info('Orphaned file cleanup completed', [
                    'total_found' => $totalOrphaned,
                    'total_deleted' => $totalDeleted,
                    'total_size' => $totalSize,
                ]);
            } else {
                $this->warn('DRY RUN: No files were actually deleted');
            }

            // Storage statistics
            $this->line('');
            $this->displayStorageStats();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during cleanup: ' . $e->getMessage());

            Log::error('Orphaned file cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Get all project directories
     *
     * @return array
     */
    protected function getProjectDirectories(): array
    {
        $directories = [];

        try {
            $allDirs = Storage::disk('public')->directories('projects');

            foreach ($allDirs as $dir) {
                $directories[] = $dir;
            }
        } catch (\Exception $e) {
            $this->error('Failed to get project directories: ' . $e->getMessage());
        }

        return $directories;
    }

    /**
     * Find orphaned files in a specific directory
     *
     * @param string $directory
     * @param int $ageDays
     * @return array
     */
    protected function findOrphanedFilesInDirectory(string $directory, int $ageDays): array
    {
        $orphaned = [];

        try {
            // Get all files in directory
            $files = Storage::disk('public')->allFiles($directory);

            // Get project ID from directory (projects/{project_id})
            $projectId = basename($directory);

            // Get all file paths for this project from database
            $dbFiles = ProjectImage::where('project_id', $projectId)
                ->pluck('file_path')
                ->toArray();

            $ageThreshold = time() - ($ageDays * 86400);

            foreach ($files as $file) {
                // Check if file exists in database
                if (!in_array($file, $dbFiles)) {
                    $lastModified = Storage::disk('public')->lastModified($file);

                    // Only include files older than threshold
                    if ($lastModified < $ageThreshold) {
                        $orphaned[] = [
                            'path' => $file,
                            'size' => Storage::disk('public')->size($file),
                            'modified' => $lastModified,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error('Failed to scan directory: ' . $e->getMessage());
        }

        return $orphaned;
    }

    /**
     * Display storage statistics
     *
     * @return void
     */
    protected function displayStorageStats(): void
    {
        $this->info('Storage Statistics:');

        $stats = $this->fileUploadService->getStorageStats();

        if (!empty($stats)) {
            $this->line('  Total space: ' . $stats['formatted']['total']);
            $this->line('  Used space: ' . $stats['formatted']['used']);
            $this->line('  Free space: ' . $stats['formatted']['free']);
            $this->line('  Usage: ' . $stats['usage_percentage'] . '%');

            // Warning if usage is high
            if ($stats['usage_percentage'] > 90) {
                $this->error('  WARNING: Storage usage is above 90%!');
            } elseif ($stats['usage_percentage'] > 80) {
                $this->warn('  WARNING: Storage usage is above 80%');
            }
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
}
