<?php

namespace App\Repositories;

use App\Models\CropProduction;
use App\Interfaces\CropProductionRepositoryInterface;

class CropProductionRepository implements CropProductionRepositoryInterface
{
    protected $model;

    public function __construct(CropProduction $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->query()->with(['region']);

        if (isset($filters['region_id'])) {
            $query->where('region_id', $filters['region_id']);
        }

        if (isset($filters['crop_name'])) {
            $query->where('crop_name', 'like', '%' . $filters['crop_name'] . '%');
        }

        if (isset($filters['fiscal_year'])) {
            $query->where('fiscal_year', $filters['fiscal_year']);
        }

        return $query->get();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->model->query()->with(['region']);

        if (isset($filters['region_id'])) {
            $query->where('region_id', $filters['region_id']);
        }

        if (isset($filters['crop_name'])) {
            $query->where('crop_name', 'like', '%' . $filters['crop_name'] . '%');
        }

        if (isset($filters['fiscal_year'])) {
            $query->where('fiscal_year', $filters['fiscal_year']);
        }

        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return $this->model->with(['region'])->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $cropProduction = $this->find($id);
        if ($cropProduction) {
            $cropProduction->update($data);
            return $cropProduction->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $cropProduction = $this->find($id);
        if ($cropProduction) {
            $cropProduction->delete();
            return $cropProduction;
        }
        return null;
    }

    public function getByRegion($regionId)
    {
        return $this->model->where('region_id', $regionId)->with(['region'])->get();
    }

    public function getByFiscalYear(int $fiscalYear)
    {
        return $this->model->where('fiscal_year', $fiscalYear)
            ->with(['region'])
            ->get();
    }
}
