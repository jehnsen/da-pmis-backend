<?php

namespace App\Services;

use App\Interfaces\NewsUpdateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NewsUpdateService
{
    public function __construct(private readonly NewsUpdateRepositoryInterface $repo)
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

    public function update(int $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->repo->delete($id);
    }

    public function getFeatured()
    {
        return $this->repo->getFeatured();
    }

    public function getPublished()
    {
        return $this->repo->getPublished();
    }
}
