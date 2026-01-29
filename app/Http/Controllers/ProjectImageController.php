<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProjectImageController extends Controller
{
    /**
     * File upload service
     *
     * @var FileUploadService
     */
    protected $fileUploadService;

    /**
     * Constructor
     */
    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Upload images for a project
     *
     * POST /api/projects/{project}/images
     */
    public function upload(Request $request, $projectId)
    {
        // Validate request
        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:' . (config('filesystems.upload_security.max_file_size') / 1024), // KB
            ],
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
            'image_types' => 'nullable|array',
            'image_types.*' => 'nullable|in:cover,progress,documentation,before,after,other',
        ]);

        $project = Project::findOrFail($projectId);

        // Check total upload size for disk space
        $totalSize = 0;
        foreach ($request->file('images') as $image) {
            $totalSize += $image->getSize();
        }

        // Check if there is sufficient disk space
        if (!$this->fileUploadService->hasSufficientDiskSpace($totalSize)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient disk space for upload. Please contact administrator.',
            ], 507); // HTTP 507 Insufficient Storage
        }

        $uploaded = [];
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($request->file('images') as $index => $image) {
                try {
                    // Upload file with all security checks
                    $uploadResult = $this->fileUploadService->uploadSecurely(
                        $image,
                        'projects/' . $projectId
                    );

                    // Create database record
                    $projectImage = ProjectImage::create([
                        'project_id' => $project->id,
                        'file_name' => $uploadResult['original_name'],
                        'file_path' => $uploadResult['filepath'],
                        'mime_type' => $uploadResult['mime_type'],
                        'file_size' => $uploadResult['size'],
                        'caption' => $request->captions[$index] ?? null,
                        'image_type' => $request->image_types[$index] ?? 'documentation',
                        'display_order' => $index,
                        'uploaded_by' => auth()->id(),
                    ]);

                    $uploaded[] = $projectImage;

                } catch (Exception $e) {
                    $errors[] = [
                        'file' => $image->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ];

                    Log::error('Image upload failed', [
                        'project_id' => $projectId,
                        'file' => $image->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // If all uploads failed, rollback
            if (empty($uploaded) && !empty($errors)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'All uploads failed',
                    'errors' => $errors,
                ], 422);
            }

            DB::commit();

            // Return response with any errors
            $response = [
                'success' => true,
                'message' => count($uploaded) . ' image(s) uploaded successfully',
                'images' => $uploaded,
            ];

            if (!empty($errors)) {
                $response['partial_errors'] = $errors;
                $response['message'] .= ' (' . count($errors) . ' failed)';
            }

            return response()->json($response, 201);

        } catch (Exception $e) {
            DB::rollBack();

            // Clean up any uploaded files
            foreach ($uploaded as $image) {
                try {
                    if (Storage::disk('public')->exists($image->file_path)) {
                        Storage::disk('public')->delete($image->file_path);
                    }
                } catch (Exception $cleanupException) {
                    Log::error('Failed to cleanup uploaded file', [
                        'file_path' => $image->file_path,
                        'error' => $cleanupException->getMessage(),
                    ]);
                }
            }

            Log::error('Image upload transaction failed', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all images for a project
     *
     * GET /api/projects/{project}/images
     */
    public function index(Request $request, $projectId)
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'image_type' => 'nullable|string|in:cover,progress,documentation,before,after,other',
        ]);

        $project = Project::findOrFail($projectId);

        $perPage = (int) $request->query('per_page', 15);

        $query = $project->images()->with('uploader:id,username,full_name')->ordered();

        // Filter by image type if provided
        if ($request->filled('image_type')) {
            $query->where('image_type', $request->image_type);
        }

        $images = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $images->items(),
            'meta' => [
                'current_page' => $images->currentPage(),
                'from' => $images->firstItem(),
                'last_page' => $images->lastPage(),
                'per_page' => $images->perPage(),
                'to' => $images->lastItem(),
                'total' => $images->total(),
            ],
            'links' => [
                'first' => $images->url(1),
                'last' => $images->url($images->lastPage()),
                'prev' => $images->previousPageUrl(),
                'next' => $images->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Delete an image
     *
     * DELETE /api/projects/{project}/images/{image}
     *
     * Note: File cleanup is handled automatically by ProjectImageObserver
     */
    public function destroy($projectId, $imageId)
    {
        try {
            $project = Project::findOrFail($projectId);
            $image = ProjectImage::where('project_id', $project->id)
                ->findOrFail($imageId);

            // Delete will trigger observer which deletes the file
            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);

        } catch (Exception $e) {
            Log::error('Image deletion failed', [
                'project_id' => $projectId,
                'image_id' => $imageId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update image details (caption, type, order)
     *
     * PUT /api/projects/{project}/images/{image}
     */
    public function update(Request $request, $projectId, $imageId)
    {
        $request->validate([
            'caption' => 'nullable|string|max:255',
            'image_type' => 'nullable|in:cover,progress,documentation,before,after,other',
            'display_order' => 'nullable|integer|min:0',
        ]);

        try {
            $project = Project::findOrFail($projectId);
            $image = ProjectImage::where('project_id', $project->id)
                ->findOrFail($imageId);

            $image->update($request->only(['caption', 'image_type', 'display_order']));

            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully',
                'image' => $image,
            ]);

        } catch (Exception $e) {
            Log::error('Image update failed', [
                'project_id' => $projectId,
                'image_id' => $imageId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get storage statistics
     *
     * GET /api/storage/stats
     */
    public function storageStats()
    {
        try {
            $stats = $this->fileUploadService->getStorageStats();

            if (empty($stats)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to retrieve storage statistics',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get storage stats', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve storage statistics',
            ], 500);
        }
    }
}
