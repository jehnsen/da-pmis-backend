# Image Upload Implementation Guide

## Overview

A complete image/photo upload system has been implemented for **Projects** and **Progress Reports**. This allows uploading multiple images per project and progress report for documentation, before/after comparisons, and progress tracking.

## Database Structure

### Tables Created

#### 1. `project_images` Table
Stores multiple images per project.

**Columns:**
- `id` - Primary key
- `project_id` - Foreign key to projects (CASCADE DELETE)
- `file_name` - Original filename
- `file_path` - Storage path (relative to storage/app/public)
- `mime_type` - File MIME type (image/jpeg, image/png, etc.)
- `file_size` - File size in bytes
- `caption` - Optional image caption/description
- `image_type` - Enum: `cover`, `progress`, `documentation`, `before`, `after`, `other`
- `display_order` - Order for displaying images (0 = first)
- `uploaded_by` - Foreign key to users (NULL ON DELETE)
- `created_at`, `updated_at` - Timestamps

**Indexes:**
- `project_id`
- `image_type`
- `display_order`

#### 2. `progress_report_images` Table
Stores multiple images per progress report.

**Columns:**
- `id` - Primary key
- `progress_report_id` - Foreign key to progress_reports (CASCADE DELETE)
- `file_name` - Original filename
- `file_path` - Storage path (relative to storage/app/public)
- `mime_type` - File MIME type
- `file_size` - File size in bytes
- `caption` - Optional image caption/description
- `image_type` - Enum: `before`, `after`, `during`, `result`, `documentation`, `other`
- `display_order` - Order for displaying images
- `uploaded_by` - Foreign key to users (NULL ON DELETE)
- `created_at`, `updated_at` - Timestamps

**Indexes:**
- `progress_report_id`
- `image_type`
- `display_order`

---

## Models

### ProjectImage Model
**Location:** `app/Models/ProjectImage.php`

**Key Features:**
- Relationship to `Project`
- Relationship to `User` (uploader)
- Image type constants with labels
- Scopes for filtering by type
- Auto-generates full URL via `url` attribute
- Human-readable file size via `file_size_human` attribute

**Usage:**
```php
$project = Project::find(1);

// Get all images
$images = $project->images;

// Get cover image
$coverImage = $project->images()->cover()->first();

// Get images by type
$progressPhotos = $project->images()->ofType('progress')->get();

// Get ordered images
$orderedImages = $project->images()->ordered()->get();
```

### ProgressReportImage Model
**Location:** `app/Models/ProgressReportImage.php`

**Key Features:**
- Relationship to `ProgressReport`
- Relationship to `User` (uploader)
- Image type constants for before/after/during/result
- Scopes for filtering
- before/after specific scope
- Auto-generates full URL and file size

**Usage:**
```php
$report = ProgressReport::find(1);

// Get all images
$images = $report->images;

// Get before/after photos
$beforeAfter = $report->images()->beforeAfter()->get();

// Get specific type
$duringPhotos = $report->images()->ofType('during')->get();
```

---

## Implementation Guide

### Step 1: Create Controller

Create `ProjectImageController.php`:

```php
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
```

### Step 2: Create Similar Controller for Progress Reports

Create `ProgressReportImageController.php` with similar methods but for progress reports.

### Step 3: Add Routes

Add to `routes/api.php`:

```php
// Project Images
Route::middleware('auth:sanctum')->group(function () {
    Route::get('projects/{project}/images', [ProjectImageController::class, 'index']);
    Route::post('projects/{project}/images', [ProjectImageController::class, 'upload']);
    Route::put('projects/{project}/images/{image}', [ProjectImageController::class, 'update']);
    Route::delete('projects/{project}/images/{image}', [ProjectImageController::class, 'destroy']);

    // Progress Report Images
    Route::get('progress-reports/{report}/images', [ProgressReportImageController::class, 'index']);
    Route::post('progress-reports/{report}/images', [ProgressReportImageController::class, 'upload']);
    Route::put('progress-reports/{report}/images/{image}', [ProgressReportImageController::class, 'update']);
    Route::delete('progress-reports/{report}/images/{image}', [ProgressReportImageController::class, 'destroy']);
});
```

### Step 4: Update API Resources

Add images to `ProjectResource.php`:

```php
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        // ... other fields ...
        'images' => ProjectImageResource::collection($this->whenLoaded('images')),
        'cover_image' => new ProjectImageResource($this->images()->cover()->first()),
    ];
}
```

Create `ProjectImageResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectImageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'url' => $this->url,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'file_size_human' => $this->file_size_human,
            'caption' => $this->caption,
            'image_type' => $this->image_type,
            'image_type_display' => $this->image_type_display,
            'display_order' => $this->display_order,
            'uploaded_by' => $this->uploaded_by,
            'uploaded_at' => $this->created_at->toISOString(),
        ];
    }
}
```

---

## API Usage Examples

### Upload Project Images

```bash
curl -X POST http://localhost:8000/api/projects/1/images \
  -H "Authorization: Bearer {token}" \
  -F "images[]=@/path/to/photo1.jpg" \
  -F "images[]=@/path/to/photo2.jpg" \
  -F "captions[]=Before construction" \
  -F "captions[]=During foundation work" \
  -F "image_types[]=before" \
  -F "image_types[]=progress"
```

### Get Project Images

```bash
curl -X GET http://localhost:8000/api/projects/1/images \
  -H "Authorization: Bearer {token}"
```

### Update Image Details

```bash
curl -X PUT http://localhost:8000/api/projects/1/images/5 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "caption": "Updated caption",
    "image_type": "cover",
    "display_order": 0
  }'
```

### Delete Image

```bash
curl -X DELETE http://localhost:8000/api/projects/1/images/5 \
  -H "Authorization: Bearer {token}"
```

---

## Frontend Integration (React Example)

```javascript
// Upload multiple images with captions
const uploadProjectImages = async (projectId, files, captions, types) => {
  const formData = new FormData();

  files.forEach((file, index) => {
    formData.append(`images[]`, file);
    formData.append(`captions[]`, captions[index] || '');
    formData.append(`image_types[]`, types[index] || 'documentation');
  });

  const response = await fetch(`/api/projects/${projectId}/images`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
    body: formData,
  });

  return await response.json();
};

// Display images
const ProjectImages = ({ projectId }) => {
  const [images, setImages] = useState([]);

  useEffect(() => {
    fetch(`/api/projects/${projectId}/images`, {
      headers: { 'Authorization': `Bearer ${token}` },
    })
      .then(res => res.json())
      .then(data => setImages(data.data));
  }, [projectId]);

  return (
    <div className="image-gallery">
      {images.map(image => (
        <div key={image.id} className="image-card">
          <img src={image.url} alt={image.caption || 'Project image'} />
          <p>{image.caption}</p>
          <small>{image.image_type_display}</small>
        </div>
      ))}
    </div>
  );
};
```

---

## Image Type Usage Guidelines

### Project Images
- **cover** - Main project photo for listings/cards
- **progress** - Photos showing construction/implementation progress
- **documentation** - General documentation photos
- **before** - Before project implementation
- **after** - After project completion
- **other** - Miscellaneous photos

### Progress Report Images
- **before** - Condition before work period
- **after** - Condition after work period
- **during** - Work in progress
- **result** - Final results/achievements
- **documentation** - Supporting documentation
- **other** - Miscellaneous

---

## File Storage

- **Storage Location:** `storage/app/public/projects/{project_id}/`
- **Progress Reports:** `storage/app/public/progress-reports/{report_id}/`
- **Max File Size:** 5MB (configurable)
- **Allowed Types:** JPEG, JPG, PNG, GIF, WEBP

### Create Symbolic Link

Run this command to make uploaded images accessible:
```bash
php artisan storage:link
```

This creates a symlink from `public/storage` to `storage/app/public`.

---

## Security Considerations

1. **File Validation:**
   - Validate MIME types
   - Check file extensions
   - Limit file size (5MB default)
   - Scan for malicious content (optional)

2. **Authentication:**
   - All upload/delete operations require authentication
   - Track who uploaded each image

3. **Storage:**
   - Store outside web root
   - Use Laravel's storage system
   - Never trust client-provided filenames

4. **Access Control:**
   - Public projects: images visible to all
   - Private projects: check permissions before serving

---

## Database Queries

```php
// Get project with images
$project = Project::with('images')->find(1);

// Get only cover image
$project = Project::with(['images' => function($query) {
    $query->cover();
}])->find(1);

// Count images per project
$projectsWithImageCounts = Project::withCount('images')->get();

// Get projects with at least one image
$projectsWithImages = Project::has('images')->get();

// Get recent uploads
$recentImages = ProjectImage::with('project')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

---

## Testing

```php
// Test image upload
public function test_can_upload_project_images()
{
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $response = $this->actingAs($user)
        ->postJson("/api/projects/{$project->id}/images", [
            'images' => [
                UploadedFile::fake()->image('photo1.jpg'),
                UploadedFile::fake()->image('photo2.jpg'),
            ],
            'captions' => ['Caption 1', 'Caption 2'],
            'image_types' => ['before', 'after'],
        ]);

    $response->assertStatus(201);
    $this->assertCount(2, $project->fresh()->images);
}
```

---

## Migration Commands

```bash
# Run migrations
php artisan migrate

# Rollback if needed
php artisan migrate:rollback

# Fresh migration (WARNING: Deletes all data)
php artisan migrate:fresh
```

---

## Summary

✅ **Implemented:**
1. Database tables for project and progress report images
2. Models with relationships and helper methods
3. Image type enums for categorization
4. Display ordering system
5. User tracking (who uploaded)
6. Auto-cascade deletion

📝 **Next Steps (Your Implementation):**
1. Create `ProjectImageController`
2. Create `ProgressReportImageController`
3. Add API routes
4. Create API resources
5. Add frontend upload forms
6. Test upload/display/delete flows

**Status:** ✅ Database & Models Ready | 📝 Controllers & Routes Needed

---

**Version:** 1.0
**Updated:** 2026-01-28
**Status:** Database Layer Complete, API Layer Pending
