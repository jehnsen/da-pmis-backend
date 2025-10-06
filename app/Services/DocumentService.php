<?php

namespace App\Services;

use App\Interfaces\DocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DocumentService
{
    public function __construct(private readonly DocumentRepositoryInterface $repo)
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

    public function search(string $query)
    {
        return $this->repo->search($query);
    }

    public function getByType(string $type)
    {
        return $this->repo->getByType($type);
    }

    public function syncCategories(int $documentId, array $categoryIds)
    {
        return $this->repo->syncCategories($documentId, $categoryIds);
    }

    public function getByCategory(int $categoryId)
    {
        return $this->repo->getByCategory($categoryId);
    }
}
