<?php

namespace App\Services;

use App\Interfaces\DepartmentReportRepositoryInterface;

class DepartmentReportService
{
    public function __construct(
        private readonly DepartmentReportRepositoryInterface $repository
    ) {}

    /**
     * Get aggregated reports for all departments
     */
    public function getDepartmentReports(array $filters = []): array
    {
        return $this->repository->getDepartmentReports($filters);
    }

    /**
     * Get monthly progress data for a specific department
     */
    public function getMonthlyProgress(int $departmentId, int $year): array
    {
        return $this->repository->getMonthlyProgress($departmentId, $year);
    }

    /**
     * Get KPI summary for a specific department
     */
    public function getKpiSummary(int $departmentId, ?int $fiscalYear = null): array
    {
        return $this->repository->getKpiSummary($departmentId, $fiscalYear);
    }

    /**
     * Get budget utilization for all departments
     */
    public function getBudgetUtilization(array $filters = []): array
    {
        return $this->repository->getBudgetUtilization($filters);
    }

    /**
     * Get department by ID
     */
    public function findDepartment(int $id)
    {
        return $this->repository->findDepartment($id);
    }
}
