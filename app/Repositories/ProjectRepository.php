<?php

namespace App\Repositories;

use App\Models\Project;
use App\Interfaces\ProjectRepositoryInterface;

class ProjectRepository implements ProjectRepositoryInterface
{
    protected $model;

    public function __construct(Project $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->query()->with(['department', 'projectType', 'projectStatus']);

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['project_type_id'])) {
            $query->where('project_type_id', $filters['project_type_id']);
        }

        if (isset($filters['project_status_id'])) {
            $query->where('project_status_id', $filters['project_status_id']);
        }

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        return $query->get();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->model->query()->with(['department', 'projectType', 'projectStatus']);

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['project_type_id'])) {
            $query->where('project_type_id', $filters['project_type_id']);
        }

        if (isset($filters['project_status_id'])) {
            $query->where('project_status_id', $filters['project_status_id']);
        }

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return $this->model->with(['department', 'projectType', 'projectStatus', 'teamMembers', 'milestones'])->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $project = $this->find($id);
        if ($project) {
            $project->update($data);
            return $project->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $project = $this->find($id);
        if ($project) {
            $project->delete();
            return $project;
        }
        return null;
    }

    public function withRelations($id, array $relations = [])
    {
        return $this->model->with($relations)->find($id);
    }

    public function getPublicProjects()
    {
        return $this->model->public()->with(['department', 'projectType', 'projectStatus'])->get();
    }

    public function getInternalProjects()
    {
        return $this->model->internal()->with(['department', 'projectType', 'projectStatus'])->get();
    }

    public function getByDepartment($departmentId)
    {
        return $this->model->where('department_id', $departmentId)
            ->with(['projectType', 'projectStatus'])
            ->get();
    }
}
