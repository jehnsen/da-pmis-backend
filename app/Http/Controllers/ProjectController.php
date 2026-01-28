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

        // Collect all possible filter parameters
        $filterKeys = [
            'search',
            'department_id',
            'project_type_id',
            'project_status_id',
            'category_id',
            'status_id',
            'region_id',
            'is_public',
            'min_budget',
            'max_budget',
            'start_date_from',
            'start_date_to',
            'end_date_from',
            'end_date_to',
            'fiscal_year',
            'sort_by',
            'sort_order',
        ];

        $filters = $request->only($filterKeys);

        // Remove null/empty values for cleaner filters_applied
        $appliedFilters = array_filter($filters, fn ($value) => $value !== null && $value !== '');

        $data = $this->service->list($perPage, $filters);

        // Return with filters_applied metadata
        return ProjectResource::collection($data)->additional([
            'filters_applied' => $appliedFilters,
        ]);
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
        return new ProjectResource($proj->load([
            'department',
            'projectType',
            'projectStatus',
            'teamMembers.user',
            'milestones',
            'documents.creator',
            'auditLogs.user'
        ]));
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
