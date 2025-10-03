<?php

namespace App\Services;

use App\Interfaces\ContactInquiryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ContactInquiryService
{
    public function __construct(private readonly ContactInquiryRepositoryInterface $repo)
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

    public function getPending()
    {
        return $this->repo->getPending();
    }

    public function getResponded()
    {
        return $this->repo->getResponded();
    }
}
