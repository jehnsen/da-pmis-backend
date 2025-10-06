<?php

namespace App\Services;

use App\Interfaces\ProgressReportRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProgressReportService
{
    public function __construct(private readonly ProgressReportRepositoryInterface $repo)
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

    public function createWithMetrics(array $reportData, array $metrics)
    {
        return $this->repo->createWithMetrics($reportData, $metrics);
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

    public function getByDepartment(int $departmentId)
    {
        return $this->repo->getByDepartment($departmentId);
    }

    public function getByPeriod(string $period)
    {
        return $this->repo->getByPeriod($period);
    }
}
