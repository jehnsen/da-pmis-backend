<?php

namespace App\Interfaces;

interface AuditLogRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function getByUser($userId);

    public function getByModel($modelType, $modelId);
}
