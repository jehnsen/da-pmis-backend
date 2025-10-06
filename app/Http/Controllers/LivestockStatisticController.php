<?php

namespace App\Http\Controllers;

use App\Http\Requests\LivestockStatistic\StoreLivestockStatisticRequest;
use App\Http\Requests\LivestockStatistic\UpdateLivestockStatisticRequest;
use App\Http\Resources\LivestockStatisticResource;
use App\Services\LivestockStatisticService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LivestockStatisticController extends Controller
{
    public function __construct(private readonly LivestockStatisticService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only(['region_id', 'livestock_type', 'fiscal_year']);
        $data = $this->service->list($perPage, $filters);
        return LivestockStatisticResource::collection($data);
    }

    public function store(StoreLivestockStatisticRequest $request): JsonResponse
    {
        try {
            $livestock = $this->service->create($request->validated());
            return (new LivestockStatisticResource($livestock->load('region')))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create livestock statistic', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $livestockStatistic): LivestockStatisticResource
    {
        $livestock = $this->service->getById($livestockStatistic);
        abort_unless($livestock, 404);
        return new LivestockStatisticResource($livestock->load('region'));
    }

    public function update(UpdateLivestockStatisticRequest $request, int $livestockStatistic): JsonResponse
    {
        try {
            $livestock = $this->service->update($livestockStatistic, $request->validated());
            return (new LivestockStatisticResource($livestock->load('region')))
                ->response();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update livestock statistic', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $livestockStatistic): JsonResponse
    {
        abort_unless($this->service->delete($livestockStatistic), 404);
        return response()->json(['message' => 'Livestock statistic deleted successfully']);
    }
}
