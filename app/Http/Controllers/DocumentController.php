<?php

namespace App\Http\Controllers;

use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only(['document_type', 'search']);
        $data = $this->service->list($perPage, $filters);
        return DocumentResource::collection($data);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        try {
            $document = $this->service->create($request->validated());
            return (new DocumentResource($document->load(['categories', 'creator'])))
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
        return new DocumentResource($doc->load(['categories', 'creator']));
    }

    public function update(UpdateDocumentRequest $request, int $document): JsonResponse
    {
        try {
            $doc = $this->service->update($document, $request->validated());
            return (new DocumentResource($doc->load(['categories', 'creator'])))
                ->response();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update document', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $document): JsonResponse
    {
        abort_unless($this->service->delete($document), 404);
        return response()->json(['message' => 'Document deleted successfully']);
    }
}
