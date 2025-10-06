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

    public function all(array $filters = [])
    {
        $query = $this->model->query()->with(['region']);

        if (isset($filters['region_id'])) {
            $query->where('region_id', $filters['region_id']);
        }

        if (isset($filters['livestock_type'])) {
            $query->where('livestock_type', 'like', '%' . $filters['livestock_type'] . '%');
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

        if (isset($filters['livestock_type'])) {
            $query->where('livestock_type', 'like', '%' . $filters['livestock_type'] . '%');
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
        $livestockStatistic = $this->find($id);
        if ($livestockStatistic) {
            $livestockStatistic->update($data);
            return $livestockStatistic->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $livestockStatistic = $this->find($id);
        if ($livestockStatistic) {
            $livestockStatistic->delete();
            return $livestockStatistic;
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
