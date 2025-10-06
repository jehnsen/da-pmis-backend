<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsUpdate\StoreNewsUpdateRequest;
use App\Http\Requests\NewsUpdate\UpdateNewsUpdateRequest;
use App\Http\Resources\NewsUpdateResource;
use App\Services\NewsUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsUpdateController extends Controller
{
    public function __construct(private readonly NewsUpdateService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only(['is_featured']);
        $data = $this->service->list($perPage, $filters);
        return NewsUpdateResource::collection($data);
    }

    public function store(StoreNewsUpdateRequest $request): JsonResponse
    {
        try {
            $news = $this->service->create($request->validated());
            return (new NewsUpdateResource($news->load('creator')))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create news update', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $newsUpdate): NewsUpdateResource
    {
        $news = $this->service->getById($newsUpdate);
        abort_unless($news, 404);
        return new NewsUpdateResource($news->load('creator'));
    }

    public function update(UpdateNewsUpdateRequest $request, int $newsUpdate): JsonResponse
    {
        try {
            $news = $this->service->update($newsUpdate, $request->validated());
            return (new NewsUpdateResource($news->load('creator')))
                ->response();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update news update', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $newsUpdate): JsonResponse
    {
        abort_unless($this->service->delete($newsUpdate), 404);
        return response()->json(['message' => 'News update deleted successfully']);
    }
}
