<?php

namespace App\Services;

use App\Interfaces\DashboardRepositoryInterface;

class DashboardService
{
    public function __construct(private readonly DashboardRepositoryInterface $repo)
    {
    }

    public function getOverview(array $filters = []): array
    {
        return $this->repo->getOverviewStats($filters);
    }

    public function getBudgetAllocation(array $filters = []): array
    {
        return $this->repo->getBudgetAllocation($filters);
    }

    public function getProjectStatusDistribution(array $filters = []): array
    {
        return $this->repo->getProjectStatusDistribution($filters);
    }

    public function getNationalPerformance(array $filters = []): array
    {
        return $this->repo->getNationalPerformance($filters);
    }

    public function getRecentUpdates(int $limit = 10): array
    {
        return $this->repo->getRecentUpdates($limit);
    }

    public function getMonthlyProgress(array $filters = []): array
    {
        return $this->repo->getMonthlyProgress($filters);
    }

    public function getPhysicalFinancialVariance(array $filters = []): array
    {
        return $this->repo->getPhysicalFinancialVariance($filters);
    }

    public function getBudgetVarianceHeatmap(array $filters = []): array
    {
        return $this->repo->getBudgetVarianceHeatmap($filters);
    }

    public function getMilestoneCompletionTracker(array $filters = []): array
    {
        return $this->repo->getMilestoneCompletionTracker($filters);
    }

    public function getTargetAchievementKpi(array $filters = []): array
    {
        return $this->repo->getTargetAchievementKpi($filters);
    }

    public function getCostEfficiencyMetrics(array $filters = []): array
    {
        return $this->repo->getCostEfficiencyMetrics($filters);
    }

    public function getRiskDashboard(array $filters = []): array
    {
        return $this->repo->getRiskDashboard($filters);
    }

    public function getBeneficiaryImpactMetrics(array $filters = []): array
    {
        return $this->repo->getBeneficiaryImpactMetrics($filters);
    }

    public function getComplianceScorecard(array $filters = []): array
    {
        return $this->repo->getComplianceScorecard($filters);
    }

    public function getYearOverYearTrends(array $filters = []): array
    {
        return $this->repo->getYearOverYearTrends($filters);
    }

    public function getEarlyWarningAlerts(array $filters = []): array
    {
        return $this->repo->getEarlyWarningAlerts($filters);
    }
}
