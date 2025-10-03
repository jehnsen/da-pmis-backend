<?php

namespace App\Services;

use App\Interfaces\AuditLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AuditLogService
{
    public function __construct(private readonly AuditLogRepositoryInterface $repo)
    {
    }

    public function list(int $perPage = 15): LengthAwarePaginator|Collection
    {
        return $this->repo->all();
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function getById(int $id)
    {
        return $this->repo->find($id);
    }

    public function getByUser(int $userId)
    {
        return $this->repo->getByUser($userId);
    }

    public function getByModel(string $modelType, int $modelId)
    {
        return $this->repo->getByModel($modelType, $modelId);
    }
}
