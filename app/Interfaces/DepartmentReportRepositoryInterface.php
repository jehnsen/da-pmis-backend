<?php

namespace App\Interfaces;

interface DepartmentReportRepositoryInterface
{
    /**
     * Get aggregated reports for all departments
     */
    public function getDepartmentReports(array $filters = []): array;

    /**
     * Get monthly progress data for a specific department
     */
    public function getMonthlyProgress(int $departmentId, int $year): array;

    /**
     * Get KPI summary for a specific department
     */
    public function getKpiSummary(int $departmentId, ?int $fiscalYear = null): array;

    /**
     * Get budget utilization for all departments
     */
    public function getBudgetUtilization(array $filters = []): array;

    /**
     * Get department by ID with relationships
     */
    public function findDepartment(int $id);
}
