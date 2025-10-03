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

    public function all()
    {
        return $this->model->with(['creator', 'categories'])->get();
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
        $document->update($data);
        return $document;
    }

    public function delete($id)
    {
        $document = $this->find($id);
        $document->delete();
        return $document;
    }

    public function getByType($type)
    {
        return $this->model->where('document_type', $type)
            ->with(['creator', 'categories'])
            ->get();
    }

    public function getByCategory($categoryId)
    {
        return $this->model->whereHas('categories', function ($query) use ($categoryId) {
            $query->where('document_categories.id', $categoryId);
        })->with(['creator', 'categories'])->get();
    }
}
