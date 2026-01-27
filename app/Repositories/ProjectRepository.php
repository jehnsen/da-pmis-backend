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

        // Search across multiple fields
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('department', function ($dq) use ($search) {
                      $dq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Department filter
        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // Project type filter (also accept category_id)
        if (isset($filters['project_type_id'])) {
            $query->where('project_type_id', $filters['project_type_id']);
        }
        if (isset($filters['category_id'])) {
            $query->where('project_type_id', $filters['category_id']);
        }

        // Status filter (accept both status_id and project_status_id)
        if (isset($filters['project_status_id'])) {
            $query->where('project_status_id', $filters['project_status_id']);
        }
        if (isset($filters['status_id'])) {
            $query->where('project_status_id', $filters['status_id']);
        }

        // Public/Private filter
        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        // Budget range filters
        if (isset($filters['min_budget'])) {
            $query->where('budget', '>=', $filters['min_budget']);
        }
        if (isset($filters['max_budget'])) {
            $query->where('budget', '<=', $filters['max_budget']);
        }

        // Date range filters
        if (isset($filters['start_date_from'])) {
            $query->where('start_date', '>=', $filters['start_date_from']);
        }
        if (isset($filters['start_date_to'])) {
            $query->where('start_date', '<=', $filters['start_date_to']);
        }
        if (isset($filters['end_date_from'])) {
            $query->where('end_date', '>=', $filters['end_date_from']);
        }
        if (isset($filters['end_date_to'])) {
            $query->where('end_date', '<=', $filters['end_date_to']);
        }

        // Fiscal year filter
        if (isset($filters['fiscal_year'])) {
            $query->whereYear('start_date', $filters['fiscal_year']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSortFields = ['title', 'budget', 'start_date', 'end_date', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
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
