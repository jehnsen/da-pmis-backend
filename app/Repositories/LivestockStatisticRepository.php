<?php

namespace App\Repositories;

use App\Models\LivestockStatistic;
use App\Interfaces\LivestockStatisticRepositoryInterface;

class LivestockStatisticRepository implements LivestockStatisticRepositoryInterface
{
    protected $model;

    public function __construct(LivestockStatistic $model)
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
        $livestockStatistic = $this->find($id);
        $livestockStatistic->update($data);
        return $livestockStatistic;
    }

    public function delete($id)
    {
        $livestockStatistic = $this->find($id);
        $livestockStatistic->delete();
        return $livestockStatistic;
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
