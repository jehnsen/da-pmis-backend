<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectMilestone\StoreProjectMilestoneRequest;
use App\Http\Requests\ProjectMilestone\UpdateProjectMilestoneRequest;
use App\Http\Resources\ProjectMilestoneResource;
use App\Services\ProjectMilestoneService;
use Illuminate\Http\JsonResponse;

class ProjectMilestoneController extends Controller
{
    public function __construct(private readonly ProjectMilestoneService $service)
    {
    }

    public function index(int $project)
    {
        $milestones = $this->service->getByProject($project);
        $completionRate = $this->service->getCompletionRate($project);

        return ProjectMilestoneResource::collection($milestones)->additional([
            'meta' => [
                'completion_rate' => $completionRate,
            ]
        ]);
    }

    public function store(StoreProjectMilestoneRequest $request, int $project): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['project_id'] = $project;

            $milestone = $this->service->create($data);
            return (new ProjectMilestoneResource($milestone))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create milestone',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $project, int $milestone): ProjectMilestoneResource
    {
        $ms = $this->service->getById($milestone);
        abort_unless($ms && $ms->project_id == $project, 404);
        return new ProjectMilestoneResource($ms);
    }

    public function update(UpdateProjectMilestoneRequest $request, int $project, int $milestone): JsonResponse
    {
        try {
            $ms = $this->service->getById($milestone);
            abort_unless($ms && $ms->project_id == $project, 404);

            $updated = $this->service->update($milestone, $request->validated());
            return (new ProjectMilestoneResource($updated))
                ->response();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update milestone',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $project, int $milestone): JsonResponse
    {
        $ms = $this->service->getById($milestone);
        abort_unless($ms && $ms->project_id == $project, 404);

        $this->service->delete($milestone);
        return response()->json(['message' => 'Milestone deleted successfully']);
    }

    public function markCompleted(int $project, int $milestone): JsonResponse
    {
        try {
            $ms = $this->service->getById($milestone);
            abort_unless($ms && $ms->project_id == $project, 404);

            $updated = $this->service->markAsCompleted($milestone);
            return (new ProjectMilestoneResource($updated))
                ->response();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark milestone as completed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
