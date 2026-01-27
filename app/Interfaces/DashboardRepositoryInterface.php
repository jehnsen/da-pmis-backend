<?php

namespace App\Interfaces;

interface DashboardRepositoryInterface
{
    public function getOverviewStats(array $filters = []): array;

    public function getBudgetAllocation(array $filters = []): array;

    public function getProjectStatusDistribution(array $filters = []): array;

    public function getNationalPerformance(array $filters = []): array;

    public function getRecentUpdates(int $limit = 10): array;

    public function getMonthlyProgress(array $filters = []): array;
}
