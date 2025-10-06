<?php

namespace App\Repositories;

use App\Models\NewsUpdate;
use App\Interfaces\NewsUpdateRepositoryInterface;

class NewsUpdateRepository implements NewsUpdateRepositoryInterface
{
    protected $model;

    public function __construct(NewsUpdate $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->query()->with(['creator']);

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->model->query()->with(['creator']);

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }

    public function find($id)
    {
        return $this->model->with(['creator'])->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $newsUpdate = $this->find($id);
        if ($newsUpdate) {
            $newsUpdate->update($data);
            return $newsUpdate->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $newsUpdate = $this->find($id);
        if ($newsUpdate) {
            $newsUpdate->delete();
            return $newsUpdate;
        }
        return null;
    }

    public function getFeatured()
    {
        return $this->model->featured()
            ->with(['creator'])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    public function getPublished()
    {
        return $this->model->published()
            ->with(['creator'])
            ->orderBy('published_at', 'desc')
            ->get();
    }
}
