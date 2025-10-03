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

    public function all()
    {
        return $this->model->with(['creator'])->orderBy('published_at', 'desc')->get();
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
        $newsUpdate->update($data);
        return $newsUpdate;
    }

    public function delete($id)
    {
        $newsUpdate = $this->find($id);
        $newsUpdate->delete();
        return $newsUpdate;
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
