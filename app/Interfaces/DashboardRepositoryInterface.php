<?php

namespace App\Interfaces;

interface DashboardRepositoryInterface
{
    public function getOverviewStats(array $filters = []): array;

    public function getBudgetAllocation(array $filters = [], $user = null): array;

    public function getBudgetAllocationBySector(array $filters = [], $user = null): array;

    public function getProjectStatusDistribution(array $filters = []): array;

    public function getNationalPerformance(array $filters = []): array;

    public function getRecentUpdates(int $limit = 10): array;

    public function getMonthlyProgress(array $filters = []): array;

    public function getPhysicalFinancialVariance(array $filters = []): array;

    public function getBudgetVarianceHeatmap(array $filters = []): array;

    public function getMilestoneCompletionTracker(array $filters = []): array;

    public function getTargetAchievementKpi(array $filters = []): array;

    public function getCostEfficiencyMetrics(array $filters = []): array;

    public function getRiskDashboard(array $filters = []): array;

    public function getBeneficiaryImpactMetrics(array $filters = []): array;

    public function getComplianceScorecard(array $filters = []): array;

    public function getYearOverYearTrends(array $filters = []): array;

    public function getEarlyWarningAlerts(array $filters = []): array;
}
