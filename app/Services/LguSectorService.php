<?php

namespace App\Services;

use App\Interfaces\LguSectorRepositoryInterface;

class LguSectorService
{
    public function __construct(private readonly LguSectorRepositoryInterface $repo)
    {
    }

    public function list(int $perPage = 15)
    {
        return $this->repo->all();
    }

    public function listActive()
    {
        return $this->repo->active();
    }

    public function listWithStatistics()
    {
        return $this->repo->withStatistics();
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
}
