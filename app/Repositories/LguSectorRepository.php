<?php

namespace App\Repositories;

use App\Interfaces\LguSectorRepositoryInterface;
use App\Models\LguSector;

class LguSectorRepository implements LguSectorRepositoryInterface
{
    protected $model;

    public function __construct(LguSector $model)
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
        $sector = $this->find($id);
        if ($sector) {
            $sector->update($data);

            return $sector;
        }

        return null;
    }

    public function delete($id)
    {
        $sector = $this->find($id);
        if ($sector) {
            $sector->delete();

            return $sector;
        }

        return null;
    }

    public function active()
    {
        return $this->model->active()->ordered()->get();
    }

    public function withStatistics()
    {
        return $this->model->active()
            ->ordered()
            ->withCount('projects')
            ->with('projects')
            ->get()
            ->map(function ($sector) {
                // Append computed attributes
                $sector->append([
                    'projects_count',
                    'approved_projects_count',
                    'total_budget',
                    'total_disbursed',
                    'utilization_rate',
                ]);

                return $sector;
            });
    }
}
