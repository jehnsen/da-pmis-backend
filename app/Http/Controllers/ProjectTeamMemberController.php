<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectTeamMember\StoreProjectTeamMemberRequest;
use App\Http\Requests\ProjectTeamMember\UpdateProjectTeamMemberRequest;
use App\Http\Resources\ProjectTeamMemberResource;
use App\Services\ProjectTeamMemberService;
use Illuminate\Http\JsonResponse;

class ProjectTeamMemberController extends Controller
{
    public function __construct(private readonly ProjectTeamMemberService $service)
    {
    }

    public function index(int $project)
    {
        $teamMembers = $this->service->getByProject($project);
        return ProjectTeamMemberResource::collection($teamMembers);
    }

    public function store(StoreProjectTeamMemberRequest $request, int $project): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['project_id'] = $project;

            // Check if user is already a team member
            $existing = $this->service->findByProjectAndUser($project, $data['user_id']);
            if ($existing) {
                return response()->json([
                    'message' => 'User is already a team member of this project'
                ], 422);
            }

            $teamMember = $this->service->create($data);
            return (new ProjectTeamMemberResource($teamMember->load('user')))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add team member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $project, int $teamMember): ProjectTeamMemberResource
    {
        $member = $this->service->getById($teamMember);
        abort_unless($member && $member->project_id == $project, 404);
        return new ProjectTeamMemberResource($member->load('user'));
    }

    public function update(UpdateProjectTeamMemberRequest $request, int $project, int $teamMember): JsonResponse
    {
        try {
            $member = $this->service->getById($teamMember);
            abort_unless($member && $member->project_id == $project, 404);

            $updated = $this->service->update($teamMember, $request->validated());
            return (new ProjectTeamMemberResource($updated->load('user')))
                ->response();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update team member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $project, int $teamMember): JsonResponse
    {
        $member = $this->service->getById($teamMember);
        abort_unless($member && $member->project_id == $project, 404);

        $this->service->delete($teamMember);
        return response()->json(['message' => 'Team member removed successfully']);
    }
}
