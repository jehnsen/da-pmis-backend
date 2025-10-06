<?php

namespace App\Interfaces;

interface AuditLogRepositoryInterface
{
    public function all(array $filters = []);

    public function find($id);

    public function create(array $data);

    public function paginate(int $perPage = 15, array $filters = []);

    public function getByUser($userId);

    public function getByModel(string $modelType, $modelId);

    public function log(string $action, string $modelType, $modelId, $userId = null, array $oldValues = [], array $newValues = []);
}
