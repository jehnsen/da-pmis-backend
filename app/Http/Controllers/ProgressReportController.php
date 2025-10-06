<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProgressReport\StoreProgressReportRequest;
use App\Http\Requests\ProgressReport\UpdateProgressReportRequest;
use App\Http\Resources\ProgressReportResource;
use App\Services\ProgressReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressReportController extends Controller
{
    public function __construct(private readonly ProgressReportService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only(['department_id', 'report_period']);
        $data = $this->service->list($perPage, $filters);
        return ProgressReportResource::collection($data);
    }

    public function store(StoreProgressReportRequest $request): JsonResponse
    {
        try {
            $report = $this->service->create($request->validated());
            return (new ProgressReportResource($report->load(['department', 'metrics', 'creator'])))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create progress report', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $progressReport): ProgressReportResource
    {
        $report = $this->service->getById($progressReport);
        abort_unless($report, 404);
        return new ProgressReportResource($report->load(['department', 'metrics', 'creator']));
    }

    public function update(UpdateProgressReportRequest $request, int $progressReport): JsonResponse
    {
        try {
            $report = $this->service->update($progressReport, $request->validated());
            return (new ProgressReportResource($report->load(['department', 'metrics'])))
                ->response();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update progress report', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $progressReport): JsonResponse
    {
        abort_unless($this->service->delete($progressReport), 404);
        return response()->json(['message' => 'Progress report deleted successfully']);
    }
}
