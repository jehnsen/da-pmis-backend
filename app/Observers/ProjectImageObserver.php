<?php

namespace App\Observers;

use App\Models\ProjectImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProjectImageObserver
{
    /**
     * Handle the ProjectImage "deleting" event.
     * This fires before the model is deleted from the database.
     *
     * @param ProjectImage $projectImage
     * @return void
     */
    public function deleting(ProjectImage $projectImage): void
    {
        try {
            // Delete the physical file from storage
            if ($projectImage->file_path && Storage::disk('public')->exists($projectImage->file_path)) {
                Storage::disk('public')->delete($projectImage->file_path);

                Log::info('File deleted automatically via observer', [
                    'file_path' => $projectImage->file_path,
                    'project_id' => $projectImage->project_id,
                    'deleted_by' => auth()->id(),
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't prevent deletion of DB record
            Log::error('Failed to delete file via observer', [
                'file_path' => $projectImage->file_path,
                'project_id' => $projectImage->project_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the ProjectImage "forceDeleted" event.
     * This fires when a soft-deleted model is permanently deleted.
     *
     * @param ProjectImage $projectImage
     * @return void
     */
    public function forceDeleted(ProjectImage $projectImage): void
    {
        try {
            // Ensure file is deleted when force deleting
            if ($projectImage->file_path && Storage::disk('public')->exists($projectImage->file_path)) {
                Storage::disk('public')->delete($projectImage->file_path);

                Log::info('File force deleted via observer', [
                    'file_path' => $projectImage->file_path,
                    'project_id' => $projectImage->project_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to force delete file via observer', [
                'file_path' => $projectImage->file_path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the ProjectImage "updating" event.
     * If file_path is changed, delete the old file.
     *
     * @param ProjectImage $projectImage
     * @return void
     */
    public function updating(ProjectImage $projectImage): void
    {
        try {
            // Check if file_path is being changed
            if ($projectImage->isDirty('file_path')) {
                $oldFilePath = $projectImage->getOriginal('file_path');

                // Delete old file if it exists
                if ($oldFilePath && Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);

                    Log::info('Old file deleted during update', [
                        'old_file_path' => $oldFilePath,
                        'new_file_path' => $projectImage->file_path,
                        'project_id' => $projectImage->project_id,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete old file during update', [
                'file_path' => $projectImage->getOriginal('file_path'),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
