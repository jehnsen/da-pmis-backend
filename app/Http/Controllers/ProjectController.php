<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only(['department_id', 'project_type_id', 'project_status_id', 'is_public']);
        $data = $this->service->list($perPage, $filters);
        return ProjectResource::collection($data);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $project = $this->service->create($request->validated());
            return (new ProjectResource($project->load(['department', 'projectType', 'projectStatus'])))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create project', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $project): ProjectResource
    {
        $proj = $this->service->getById($project);
        abort_unless($proj, 404);
        return new ProjectResource($proj->load(['department', 'projectType', 'projectStatus', 'teamMembers.user', 'milestones']));
    }

    public function update(UpdateProjectRequest $request, int $project): JsonResponse
    {
        try {
            $proj = $this->service->update($project, $request->validated());
            return (new ProjectResource($proj->load(['department', 'projectType', 'projectStatus'])))
                ->response();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update project', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $project): JsonResponse
    {
        abort_unless($this->service->delete($project), 404);
        return response()->json(['message' => 'Project deleted successfully']);
    }
}
