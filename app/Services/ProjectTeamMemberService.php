<?php

namespace App\Services;

use App\Interfaces\ProjectTeamMemberRepositoryInterface;

class ProjectTeamMemberService
{
    public function __construct(private readonly ProjectTeamMemberRepositoryInterface $repo)
    {
    }

    public function getByProject(int $projectId)
    {
        return $this->repo->findByProject($projectId);
    }

    public function getById(int $id)
    {
        return $this->repo->find($id);
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->repo->delete($id);
    }

    public function findByProjectAndUser(int $projectId, int $userId)
    {
        return $this->repo->findByProjectAndUser($projectId, $userId);
    }

    public function deleteByProjectAndUser(int $projectId, int $userId)
    {
        return $this->repo->deleteByProjectAndUser($projectId, $userId);
    }
}
