<?php

namespace App\Services;

use App\Interfaces\UserManagementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserManagementService
{
    public function __construct(private readonly UserManagementRepositoryInterface $repo)
    {
    }

    public function list(int $perPage = 15, array $filters = []): LengthAwarePaginator|Collection
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function getById(int $id)
    {
        return $this->repo->find($id);
    }

    public function update(int $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->repo->delete($id);
    }

    public function toggleStatus(int $id)
    {
        return $this->repo->toggleStatus($id);
    }

    public function getStatistics()
    {
        return $this->repo->getStatistics();
    }

    public function getSummary(array $filters = [])
    {
        return $this->repo->getSummary($filters);
    }

    public function updateLastLogin(int $id)
    {
        return $this->repo->updateLastLogin($id);
    }
}
