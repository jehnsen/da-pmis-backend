<?php

namespace App\Http\Controllers;

use App\Http\Requests\CropProduction\StoreCropProductionRequest;
use App\Http\Requests\CropProduction\UpdateCropProductionRequest;
use App\Http\Resources\CropProductionResource;
use App\Services\CropProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CropProductionController extends Controller
{
    public function __construct(private readonly CropProductionService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only(['region_id', 'crop_name', 'fiscal_year']);
        $data = $this->service->list($perPage, $filters);
        return CropProductionResource::collection($data);
    }

    public function store(StoreCropProductionRequest $request): JsonResponse
    {
        try {
            $crop = $this->service->create($request->validated());
            return (new CropProductionResource($crop->load('region')))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create crop production record', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $cropProduction): CropProductionResource
    {
        $crop = $this->service->getById($cropProduction);
        abort_unless($crop, 404);
        return new CropProductionResource($crop->load('region'));
    }

    public function update(UpdateCropProductionRequest $request, int $cropProduction): JsonResponse
    {
        try {
            $crop = $this->service->update($cropProduction, $request->validated());
            return (new CropProductionResource($crop->load('region')))
                ->response();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update crop production record', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $cropProduction): JsonResponse
    {
        abort_unless($this->service->delete($cropProduction), 404);
        return response()->json(['message' => 'Crop production record deleted successfully']);
    }
}
