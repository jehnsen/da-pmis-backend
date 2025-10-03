<?php

namespace App\Repositories;

use App\Models\ProgressReport;
use App\Interfaces\ProgressReportRepositoryInterface;

class ProgressReportRepository implements ProgressReportRepositoryInterface
{
    protected $model;

    public function __construct(ProgressReport $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->with(['department', 'creator', 'metrics'])->get();
    }

    public function find($id)
    {
        return $this->model->with(['department', 'creator', 'metrics'])->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $report = $this->find($id);
        $report->update($data);
        return $report;
    }

    public function delete($id)
    {
        $report = $this->find($id);
        $report->delete();
        return $report;
    }

    public function getByDepartment($departmentId)
    {
        return $this->model->where('department_id', $departmentId)
            ->with(['creator', 'metrics'])
            ->orderBy('report_date', 'desc')
            ->get();
    }

    public function getByPeriod($period)
    {
        return $this->model->where('report_period', $period)
            ->with(['department', 'creator', 'metrics'])
            ->orderBy('report_date', 'desc')
            ->get();
    }
}
