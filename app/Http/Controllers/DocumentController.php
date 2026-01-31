<?php

namespace App\Http\Controllers;

use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        $filterKeys = [
            'search',
            'document_type',
            'type',
            'category',
            'category_id',
            'department_id',
            'project_id',
            'fiscal_year',
            'is_featured',
            'sort_by',
        ];

        $filters = $request->only($filterKeys);
        $appliedFilters = array_filter($filters, fn ($value) => $value !== null && $value !== '');

        $data = $this->service->list($perPage, $filters);

        return DocumentResource::collection($data)->additional([
            'filters_applied' => $appliedFilters,
        ]);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('documents', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['mime_type'] = $file->getMimeType();
                $data['file_size'] = $file->getSize();
            }

            // Set creator if authenticated
            if (auth()->check()) {
                $data['created_by'] = auth()->id();
            }

            // Set default values
            $data['download_count'] = 0;

            $document = $this->service->create($data);

            // Sync categories if provided
            if (isset($data['categories']) && is_array($data['categories'])) {
                $this->service->syncCategories($document->id, $data['categories']);
            }

            return (new DocumentResource($document->load(['categories', 'creator', 'category', 'department'])))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create document', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $document): DocumentResource
    {
        $doc = $this->service->getById($document);
        abort_unless($doc, 404);

        return new DocumentResource($doc->load(['categories', 'creator', 'category', 'department']));
    }

    public function update(UpdateDocumentRequest $request, int $document): JsonResponse
    {
        try {
            $data = $request->validated();

            // Handle file upload if new file is provided
            if ($request->hasFile('file')) {
                $existingDoc = $this->service->getById($document);

                // Delete old file if exists
                if ($existingDoc && $existingDoc->file_path) {
                    Storage::disk('public')->delete($existingDoc->file_path);
                }

                $file = $request->file('file');
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('documents', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['mime_type'] = $file->getMimeType();
                $data['file_size'] = $file->getSize();
            }

            $doc = $this->service->update($document, $data);

            // Sync categories if provided
            if (isset($data['categories']) && is_array($data['categories'])) {
                $this->service->syncCategories($document, $data['categories']);
            }

            return (new DocumentResource($doc->load(['categories', 'creator', 'category', 'department'])))
                ->response();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update document', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $document): JsonResponse
    {
        $doc = $this->service->getById($document);

        if (! $doc) {
            abort(404);
        }

        // Delete file from storage
        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $this->service->delete($document);

        return response()->json(['message' => 'Document deleted successfully']);
    }

    /**
     * Download a document and increment download count
     */
    public function download(int $document): StreamedResponse|JsonResponse
    {
        $doc = $this->service->getById($document);

        if (! $doc) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        if (! $doc->file_path || ! Storage::disk('public')->exists($doc->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Increment download count
        $this->service->incrementDownloadCount($document);

        $fileName = $doc->file_name ?? basename($doc->file_path);

        return Storage::disk('public')->download($doc->file_path, $fileName);
    }

    /**
     * Get featured documents for homepage
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);
        $limit = min(max($limit, 1), 50);

        $documents = $this->service->getFeatured($limit);

        return response()->json([
            'success' => true,
            'data' => DocumentResource::collection($documents),
            'message' => 'Featured documents retrieved successfully',
        ]);
    }
}
