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

    public function all()
    {
        return $this->model->with(['department', 'projectType', 'projectStatus'])->get();
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
        $project->update($data);
        return $project;
    }

    public function delete($id)
    {
        $project = $this->find($id);
        $project->delete();
        return $project;
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
