<?php

namespace App\Services;

use App\Interfaces\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProjectService
{
    public function __construct(private readonly ProjectRepositoryInterface $repo)
    {
    }

    public function list(int $perPage = 15, array $filters = [], $user = null): LengthAwarePaginator|Collection
    {
        return $this->repo->paginate($perPage, $filters, $user);
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function getById(int $id, $user = null)
    {
        return $this->repo->find($id, $user);
    }

    public function update(int $id, array $data, $user = null)
    {
        return $this->repo->update($id, $data, $user);
    }

    public function delete(int $id, $user = null)
    {
        return $this->repo->delete($id, $user);
    }

    public function getPublicProjects()
    {
        return $this->repo->getPublicProjects();
    }

    public function getInternalProjects()
    {
        return $this->repo->getInternalProjects();
    }

    public function getByDepartment(int $departmentId)
    {
        return $this->repo->getByDepartment($departmentId);
    }
}
