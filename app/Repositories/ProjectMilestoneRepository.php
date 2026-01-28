<?php

namespace App\Repositories;

use App\Models\ProjectMilestone;
use App\Interfaces\ProjectMilestoneRepositoryInterface;

class ProjectMilestoneRepository implements ProjectMilestoneRepositoryInterface
{
    protected $model;

    public function __construct(ProjectMilestone $model)
    {
        $this->model = $model;
    }

    public function findByProject($projectId)
    {
        return $this->model->where('project_id', $projectId)
            ->orderBy('target_date')
            ->get();
    }

    public function find($id)
    {
        return $this->model->with('project')->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $milestone = $this->find($id);
        if ($milestone) {
            $milestone->update($data);
            return $milestone->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $milestone = $this->find($id);
        if ($milestone) {
            $milestone->delete();
            return true;
        }
        return false;
    }

    public function getCompletionRate($projectId)
    {
        $total = $this->model->where('project_id', $projectId)->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->model->where('project_id', $projectId)
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    public function markAsCompleted($id)
    {
        $milestone = $this->find($id);
        if ($milestone) {
            $milestone->update([
                'status' => 'completed',
                'completion_date' => now(),
            ]);
            return $milestone->fresh();
        }
        return null;
    }
}
