<?php

namespace App\Repositories;

use App\Models\Document;
use App\Interfaces\DocumentRepositoryInterface;

class DocumentRepository implements DocumentRepositoryInterface
{
    protected $model;

    public function __construct(Document $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->query()->with(['creator', 'categories']);

        if (isset($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->get();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->model->query()->with(['creator', 'categories']);

        if (isset($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return $this->model->with(['creator', 'categories'])->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $document = $this->find($id);
        if ($document) {
            $document->update($data);
            return $document->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $document = $this->find($id);
        if ($document) {
            $document->delete();
            return $document;
        }
        return null;
    }

    public function search(string $query)
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('title', 'like', '%' . $query . '%')
              ->orWhere('description', 'like', '%' . $query . '%')
              ->orWhere('file_name', 'like', '%' . $query . '%');
        })->with(['creator', 'categories'])->get();
    }

    public function getByType(string $type)
    {
        return $this->model->where('document_type', $type)
            ->with(['creator', 'categories'])
            ->get();
    }

    public function syncCategories($documentId, array $categoryIds)
    {
        $document = $this->find($documentId);
        if ($document) {
            $document->categories()->sync($categoryIds);
            return $document->load('categories');
        }
        return null;
    }

    public function getByCategory($categoryId)
    {
        return $this->model->whereHas('categories', function ($query) use ($categoryId) {
            $query->where('document_categories.id', $categoryId);
        })->with(['creator', 'categories'])->get();
    }
}
