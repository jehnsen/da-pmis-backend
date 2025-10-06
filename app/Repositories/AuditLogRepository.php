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

    public function all(array $filters = [])
    {
        $query = $this->model->query()->with(['user']);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->model->query()->with(['user']);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
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
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByModel(string $modelType, $modelId)
    {
        return $this->model->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function log(string $action, string $modelType, $modelId, $userId = null, array $oldValues = [], array $newValues = [])
    {
        return $this->model->create([
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'user_id' => $userId ?? auth()->id(),
            'old_values' => !empty($oldValues) ? json_encode($oldValues) : null,
            'new_values' => !empty($newValues) ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
