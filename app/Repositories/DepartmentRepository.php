<?php
namespace App\Repositories;

use App\Models\Department;
use App\Interfaces\DepartmentRepositoryInterface;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    protected $model;

    public function __construct(Department $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $department = $this->find($id);
        $department->update($data);
        return $department;
    }

    public function delete($id)
    {
        $department = $this->find($id);
        $department->delete();
        return $department;
    }
}