<?php

namespace App\Http\Controllers;

use App\Http\Requests\LguSector\StoreLguSectorRequest;
use App\Http\Requests\LguSector\UpdateLguSectorRequest;
use App\Http\Resources\LguSectorResource;
use App\Services\LguSectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LguSectorController extends Controller
{
    public function __construct(private readonly LguSectorService $service)
    {
    }

    /**
     * List all LGU sectors
     * GET /api/sectors?include_stats=true
     */
    public function index(Request $request)
    {
        $includeStats = $request->query('include_stats') === 'true';

        $data = $includeStats
            ? $this->service->listWithStatistics()
            : $this->service->listActive(); // Default: only active sectors

        return LguSectorResource::collection($data);
    }

    /**
     * Create a new LGU sector
     */
    public function store(StoreLguSectorRequest $request): JsonResponse
    {
        $sector = $this->service->create($request->validated());

        return (new LguSectorResource($sector))->response()->setStatusCode(201);
    }

    /**
     * Show a specific LGU sector
     */
    public function show(int $sector, Request $request): LguSectorResource
    {
        $sectorModel = $this->service->getById($sector);
        abort_unless($sectorModel, 404);

        return new LguSectorResource($sectorModel);
    }

    /**
     * Update an existing LGU sector
     */
    public function update(UpdateLguSectorRequest $request, int $sector): LguSectorResource
    {
        $sectorModel = $this->service->update($sector, $request->validated());

        return new LguSectorResource($sectorModel);
    }

    /**
     * Delete an LGU sector
     */
    public function destroy(int $sector): JsonResponse
    {
        abort_unless($this->service->delete($sector), 404);

        return response()->json(['message' => 'LGU Sector deleted successfully']);
    }
}
