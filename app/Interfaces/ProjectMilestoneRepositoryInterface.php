<?php

namespace App\Interfaces;

interface ProjectMilestoneRepositoryInterface
{
    public function findByProject($projectId);

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function getCompletionRate($projectId);

    public function markAsCompleted($id);
}
