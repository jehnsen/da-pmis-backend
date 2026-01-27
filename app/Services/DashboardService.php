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
}
