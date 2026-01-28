<?php

namespace App\Repositories;

use App\Models\ProjectTeamMember;
use App\Interfaces\ProjectTeamMemberRepositoryInterface;

class ProjectTeamMemberRepository implements ProjectTeamMemberRepositoryInterface
{
    protected $model;

    public function __construct(ProjectTeamMember $model)
    {
        $this->model = $model;
    }

    public function findByProject($projectId)
    {
        return $this->model->where('project_id', $projectId)
            ->with('user')
            ->get();
    }

    public function find($id)
    {
        return $this->model->with('user', 'project')->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $teamMember = $this->find($id);
        if ($teamMember) {
            $teamMember->update($data);
            return $teamMember->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $teamMember = $this->find($id);
        if ($teamMember) {
            $teamMember->delete();
            return true;
        }
        return false;
    }

    public function findByProjectAndUser($projectId, $userId)
    {
        return $this->model->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->first();
    }

    public function deleteByProjectAndUser($projectId, $userId)
    {
        return $this->model->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->delete();
    }
}
