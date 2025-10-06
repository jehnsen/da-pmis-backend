<?php

namespace App\Services;

use App\Interfaces\LivestockStatisticRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LivestockStatisticService
{
    public function __construct(private readonly LivestockStatisticRepositoryInterface $repo)
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

    public function getByRegion(int $regionId)
    {
        return $this->repo->getByRegion($regionId);
    }

    public function getByFiscalYear(int $fiscalYear)
    {
        return $this->repo->getByFiscalYear($fiscalYear);
    }
}
