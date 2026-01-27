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
        $query = $this->model->query()->with(['creator', 'categories', 'category', 'department']);

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('file_name', 'like', '%' . $search . '%');
            });
        }

        // Document type filter
        if (isset($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }
        if (isset($filters['type'])) {
            $query->where('document_type', $filters['type']);
        }

        // Category filter (by ID or slug)
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (isset($filters['category'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('slug', $filters['category'])
                  ->orWhere('name', 'like', '%' . $filters['category'] . '%');
            });
        }

        // Department filter
        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // Fiscal year filter
        if (isset($filters['fiscal_year'])) {
            $query->where('fiscal_year', $filters['fiscal_year']);
        }

        // Featured filter
        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'newest';
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('published_date', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('published_date', 'asc')->orderBy('created_at', 'asc');
                break;
            case 'popular':
                $query->orderBy('download_count', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
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

    public function getFeatured(int $limit = 10)
    {
        return $this->model->featured()
            ->with(['creator', 'category', 'department'])
            ->orderBy('published_date', 'desc')
            ->limit($limit)
            ->get();
    }

    public function incrementDownloadCount($id): ?Document
    {
        $document = $this->model->find($id);
        if ($document) {
            $document->increment('download_count');

            return $document->fresh();
        }

        return null;
    }
}
