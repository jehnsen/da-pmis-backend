<?php

namespace App\Interfaces;

use App\Models\Project;
use App\Models\ProjectDisbursement;

interface ProjectDisbursementRepositoryInterface
{
    public function getByProject(int $projectId, int $perPage = 15);

    public function create(array $data): ProjectDisbursement;

    public function update(ProjectDisbursement $disbursement, array $data): ProjectDisbursement;

    public function delete(ProjectDisbursement $disbursement): bool;

    public function getFinancialSummary(Project $project): array;

    public function getDisbursementsByCategory(Project $project): array;

    public function getMonthlySpending(Project $project, int $year = null): array;

    public function updateStatus(ProjectDisbursement $disbursement, string $status, ?int $approvedBy = null): ProjectDisbursement;

    public function checkBudgetThreshold(Project $project): ?string;
}
