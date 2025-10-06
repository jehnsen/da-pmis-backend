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

    public function all(array $filters = [])
    {
        $query = $this->model->query()->with(['department', 'creator', 'metrics']);

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['report_period'])) {
            $query->where('report_period', $filters['report_period']);
        }

        return $query->orderBy('report_date', 'desc')->get();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->model->query()->with(['department', 'creator', 'metrics']);

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['report_period'])) {
            $query->where('report_period', $filters['report_period']);
        }

        return $query->orderBy('report_date', 'desc')->paginate($perPage);
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
        if ($report) {
            $report->update($data);
            return $report->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $report = $this->find($id);
        if ($report) {
            $report->delete();
            return $report;
        }
        return null;
    }

    public function createWithMetrics(array $reportData, array $metrics)
    {
        return \DB::transaction(function () use ($reportData, $metrics) {
            $report = $this->model->create($reportData);

            if (!empty($metrics)) {
                $report->metrics()->createMany($metrics);
            }

            return $report->load(['department', 'creator', 'metrics']);
        });
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
