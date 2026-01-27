<?php

namespace App\Services;

use App\Interfaces\ProjectDisbursementRepositoryInterface;
use App\Models\Project;
use App\Models\ProjectDisbursement;

class ProjectDisbursementService
{
    public function __construct(
        protected ProjectDisbursementRepositoryInterface $repository
    ) {}

    public function getByProject(int $projectId, int $perPage = 15)
    {
        return $this->repository->getByProject($projectId, $perPage);
    }

    public function create(array $data): ProjectDisbursement
    {
        return $this->repository->create($data);
    }

    public function update(ProjectDisbursement $disbursement, array $data): ProjectDisbursement
    {
        return $this->repository->update($disbursement, $data);
    }

    public function delete(ProjectDisbursement $disbursement): bool
    {
        return $this->repository->delete($disbursement);
    }

    public function getFinancialSummary(Project $project): array
    {
        return $this->repository->getFinancialSummary($project);
    }

    public function getDisbursementsByCategory(Project $project): array
    {
        return $this->repository->getDisbursementsByCategory($project);
    }

    public function getMonthlySpending(Project $project, ?int $year = null): array
    {
        return $this->repository->getMonthlySpending($project, $year);
    }

    public function updateStatus(ProjectDisbursement $disbursement, string $status, ?int $approvedBy = null): ProjectDisbursement
    {
        return $this->repository->updateStatus($disbursement, $status, $approvedBy);
    }

    public function checkBudgetThreshold(Project $project): ?string
    {
        return $this->repository->checkBudgetThreshold($project);
    }
}
