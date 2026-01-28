<?php

namespace App\Services;

use App\Interfaces\ProjectMilestoneRepositoryInterface;

class ProjectMilestoneService
{
    public function __construct(private readonly ProjectMilestoneRepositoryInterface $repo)
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

    public function getCompletionRate(int $projectId)
    {
        return $this->repo->getCompletionRate($projectId);
    }

    public function markAsCompleted(int $id)
    {
        return $this->repo->markAsCompleted($id);
    }
}
