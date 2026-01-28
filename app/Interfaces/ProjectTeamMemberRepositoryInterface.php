<?php

namespace App\Interfaces;

interface ProjectTeamMemberRepositoryInterface
{
    public function findByProject($projectId);

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function findByProjectAndUser($projectId, $userId);

    public function deleteByProjectAndUser($projectId, $userId);
}
