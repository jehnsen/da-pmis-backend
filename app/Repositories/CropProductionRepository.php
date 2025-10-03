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

    public function all()
    {
        return $this->model->with(['region'])->get();
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
        $cropProduction->update($data);
        return $cropProduction;
    }

    public function delete($id)
    {
        $cropProduction = $this->find($id);
        $cropProduction->delete();
        return $cropProduction;
    }

    public function getByRegion($regionId)
    {
        return $this->model->where('region_id', $regionId)->get();
    }

    public function getByFiscalYear($fiscalYear)
    {
        return $this->model->where('fiscal_year', $fiscalYear)
            ->with(['region'])
            ->get();
    }
}
