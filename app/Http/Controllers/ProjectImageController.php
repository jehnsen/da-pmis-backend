<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectImageController extends Controller
{
    /**
     * Upload images for a project
     */
    public function upload(Request $request, $projectId)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
            'image_types' => 'nullable|array',
            'image_types.*' => 'nullable|in:cover,progress,documentation,before,after,other',
        ]);

        $project = Project::findOrFail($projectId);
        $uploaded = [];

        foreach ($request->file('images') as $index => $image) {
            $fileName = time() . '_' . $index . '_' . $image->getClientOriginalName();
            $filePath = $image->storeAs('projects/' . $projectId, $fileName, 'public');

            $projectImage = ProjectImage::create([
                'project_id' => $project->id,
                'file_name' => $image->getClientOriginalName(),
                'file_path' => $filePath,
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize(),
                'caption' => $request->captions[$index] ?? null,
                'image_type' => $request->image_types[$index] ?? 'documentation',
                'display_order' => $index,
                'uploaded_by' => auth()->id(),
            ]);

            $uploaded[] = $projectImage;
        }

        return response()->json([
            'message' => count($uploaded) . ' image(s) uploaded successfully',
            'images' => $uploaded,
        ], 201);
    }

    /**
     * Get all images for a project
     */
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
        $images = $project->images()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }

    /**
     * Delete an image
     */
    public function destroy($projectId, $imageId)
    {
        $project = Project::findOrFail($projectId);
        $image = ProjectImage::where('project_id', $project->id)
            ->findOrFail($imageId);

        // Delete file from storage
        if (Storage::disk('public')->exists($image->file_path)) {
            Storage::disk('public')->delete($image->file_path);
        }

        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully',
        ]);
    }

    /**
     * Update image details (caption, type, order)
     */
    public function update(Request $request, $projectId, $imageId)
    {
        $request->validate([
            'caption' => 'nullable|string|max:255',
            'image_type' => 'nullable|in:cover,progress,documentation,before,after,other',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $project = Project::findOrFail($projectId);
        $image = ProjectImage::where('project_id', $project->id)
            ->findOrFail($imageId);

        $image->update($request->only(['caption', 'image_type', 'display_order']));

        return response()->json([
            'message' => 'Image updated successfully',
            'image' => $image,
        ]);
    }
}