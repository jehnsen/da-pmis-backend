<?php

namespace App\Console\Commands;

use App\Services\FileUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MonitorStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:monitor
                            {--warn-threshold=80 : Warning threshold percentage (default: 80)}
                            {--critical-threshold=90 : Critical threshold percentage (default: 90)}
                            {--disk=public : Disk to monitor (default: public)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor storage usage and alert if thresholds are exceeded';

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
        $disk = $this->option('disk');
        $warnThreshold = (int) $this->option('warn-threshold');
        $criticalThreshold = (int) $this->option('critical-threshold');

        $this->info('Monitoring storage for disk: ' . $disk);
        $this->line('');

        try {
            $stats = $this->fileUploadService->getStorageStats($disk);

            if (empty($stats)) {
                $this->error('Failed to retrieve storage statistics');
                return Command::FAILURE;
            }

            // Display statistics
            $this->displayStorageStats($stats);

            // Check thresholds
            $usagePercentage = $stats['usage_percentage'];

            if ($usagePercentage >= $criticalThreshold) {
                $this->error('CRITICAL: Storage usage is at ' . $usagePercentage . '%!');
                $this->error('Immediate action required!');

                Log::critical('Storage usage critical', [
                    'disk' => $disk,
                    'usage_percentage' => $usagePercentage,
                    'free_space' => $stats['free'],
                    'total_space' => $stats['total'],
                ]);

                return Command::FAILURE;

            } elseif ($usagePercentage >= $warnThreshold) {
                $this->warn('WARNING: Storage usage is at ' . $usagePercentage . '%');
                $this->warn('Consider cleaning up old files or expanding storage');

                Log::warning('Storage usage high', [
                    'disk' => $disk,
                    'usage_percentage' => $usagePercentage,
                    'free_space' => $stats['free'],
                    'total_space' => $stats['total'],
                ]);

                return Command::SUCCESS;

            } else {
                $this->info('Storage usage is healthy at ' . $usagePercentage . '%');

                Log::info('Storage monitoring completed', [
                    'disk' => $disk,
                    'usage_percentage' => $usagePercentage,
                    'free_space' => $stats['free'],
                ]);

                return Command::SUCCESS;
            }

        } catch (\Exception $e) {
            $this->error('Error monitoring storage: ' . $e->getMessage());

            Log::error('Storage monitoring failed', [
                'disk' => $disk,
                'error' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Display storage statistics
     *
     * @param array $stats
     * @return void
     */
    protected function displayStorageStats(array $stats): void
    {
        $this->line('=================================================');
        $this->info('Storage Statistics:');
        $this->line('=================================================');
        $this->line('Total space:    ' . $stats['formatted']['total']);
        $this->line('Used space:     ' . $stats['formatted']['used']);
        $this->line('Free space:     ' . $stats['formatted']['free']);
        $this->line('Usage:          ' . $stats['usage_percentage'] . '%');
        $this->line('=================================================');
        $this->line('');

        // Visual progress bar
        $this->displayProgressBar($stats['usage_percentage']);
        $this->line('');
    }

    /**
     * Display visual progress bar for storage usage
     *
     * @param float $percentage
     * @return void
     */
    protected function displayProgressBar(float $percentage): void
    {
        $barLength = 50;
        $filledLength = (int) round(($percentage / 100) * $barLength);
        $emptyLength = $barLength - $filledLength;

        $bar = str_repeat('█', $filledLength) . str_repeat('░', $emptyLength);

        // Color based on percentage
        if ($percentage >= 90) {
            $this->error('Usage: [' . $bar . '] ' . $percentage . '%');
        } elseif ($percentage >= 80) {
            $this->warn('Usage: [' . $bar . '] ' . $percentage . '%');
        } else {
            $this->info('Usage: [' . $bar . '] ' . $percentage . '%');
        }
    }
}
