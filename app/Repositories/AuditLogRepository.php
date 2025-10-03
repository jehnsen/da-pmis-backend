<?php

namespace App\Repositories;

use App\Models\AuditLog;
use App\Interfaces\AuditLogRepositoryInterface;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    protected $model;

    public function __construct(AuditLog $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->with(['user'])->orderBy('created_at', 'desc')->get();
    }

    public function find($id)
    {
        return $this->model->with(['user'])->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getByUser($userId)
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByModel($modelType, $modelId)
    {
        return $this->model->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
