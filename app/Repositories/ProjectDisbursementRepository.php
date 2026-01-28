<?php

namespace App\Repositories;

use App\Interfaces\ProjectDisbursementRepositoryInterface;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectDisbursement;
use App\Notifications\BudgetAlertNotification;
use Illuminate\Support\Facades\DB;

class ProjectDisbursementRepository implements ProjectDisbursementRepositoryInterface
{
    /**
     * Budget threshold percentages for alerts
     */
    private array $thresholds = [
        'warning' => 75,
        'critical' => 90,
        'exceeded' => 100,
    ];

    public function getByProject(int $projectId, int $perPage = 15)
    {
        return ProjectDisbursement::where('project_id', $projectId)
            ->with(['project', 'createdBy', 'approvedBy'])
            ->orderBy('disbursement_date', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): ProjectDisbursement
    {
        return DB::transaction(function () use ($data) {
            $disbursement = ProjectDisbursement::create($data);

            // Create audit log
            $this->createAuditLog($disbursement, 'created');

            // Check budget threshold after creating disbursement
            $this->checkAndNotifyBudgetThreshold($disbursement->project);

            return $disbursement->fresh(['project', 'createdBy']);
        });
    }

    public function update(ProjectDisbursement $disbursement, array $data): ProjectDisbursement
    {
        return DB::transaction(function () use ($disbursement, $data) {
            $oldValues = $disbursement->toArray();
            $disbursement->update($data);

            // Create audit log
            $this->createAuditLog($disbursement, 'updated', $oldValues);

            // Check budget threshold after updating
            $this->checkAndNotifyBudgetThreshold($disbursement->project);

            return $disbursement->fresh(['project', 'createdBy', 'approvedBy']);
        });
    }

    public function delete(ProjectDisbursement $disbursement): bool
    {
        return DB::transaction(function () use ($disbursement) {
            $this->createAuditLog($disbursement, 'deleted');

            return $disbursement->delete();
        });
    }

    public function getFinancialSummary(Project $project): array
    {
        $totalBudget = (float) $project->budget;
        $totalDisbursed = $project->total_disbursed;
        $remainingBudget = $project->remaining_budget;
        $utilizationRate = $project->utilization_rate;

        $disbursementsByCategory = $this->getDisbursementsByCategory($project);
        $monthlySpending = $this->getMonthlySpending($project);

        $pendingDisbursements = $project->disbursements()
            ->where('status', 'pending')
            ->sum('amount');

        $completedDisbursements = $project->disbursements()
            ->where('status', 'completed')
            ->count();

        return [
            'project_id' => $project->id,
            'project_title' => $project->title,
            'total_budget' => $totalBudget,
            'total_disbursed' => $totalDisbursed,
            'remaining_budget' => $remainingBudget,
            'utilization_rate' => $utilizationRate,
            'pending_disbursements' => (float) $pendingDisbursements,
            'completed_disbursement_count' => $completedDisbursements,
            'budget_status' => $this->getBudgetStatus($utilizationRate),
            'disbursements_by_category' => $disbursementsByCategory,
            'monthly_spending' => $monthlySpending,
            'variance_analysis' => $this->getVarianceAnalysis($project, $disbursementsByCategory),
        ];
    }

    public function getDisbursementsByCategory(Project $project): array
    {
        $categories = $project->disbursements()
            ->where('status', 'completed')
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->reorder() // Clear inherited ORDER BY from relationship
            ->get();

        $result = [];
        foreach (ProjectDisbursement::CATEGORIES as $key => $label) {
            $categoryData = $categories->firstWhere('category', $key);
            $result[$key] = [
                'label' => $label,
                'total' => (float) ($categoryData?->total ?? 0),
                'count' => (int) ($categoryData?->count ?? 0),
            ];
        }

        return $result;
    }

    public function getMonthlySpending(Project $project, ?int $year = null): array
    {
        $year = $year ?? now()->year;

        $monthlyData = $project->disbursements()
            ->where('status', 'completed')
            ->whereYear('disbursement_date', $year)
            ->select(
                DB::raw('MONTH(disbursement_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy(DB::raw('MONTH(disbursement_date)'))
            ->reorder() // Clear inherited ORDER BY from relationship
            ->get()
            ->keyBy('month');

        $result = [];
        for ($month = 1; $month <= 12; $month++) {
            $result[] = [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'total' => (float) ($monthlyData->get($month)?->total ?? 0),
            ];
        }

        return $result;
    }

    public function updateStatus(ProjectDisbursement $disbursement, string $status, ?int $approvedBy = null): ProjectDisbursement
    {
        return DB::transaction(function () use ($disbursement, $status, $approvedBy) {
            $oldStatus = $disbursement->status;

            $updateData = ['status' => $status];
            if ($status === 'completed' && $approvedBy) {
                $updateData['approved_by'] = $approvedBy;
                $updateData['approved_at'] = now();
            }

            $disbursement->update($updateData);

            // Create audit log
            $this->createAuditLog($disbursement, 'status_updated', ['status' => $oldStatus]);

            // Check budget threshold if disbursement is completed
            if ($status === 'completed') {
                $this->checkAndNotifyBudgetThreshold($disbursement->project);
            }

            return $disbursement->fresh(['project', 'createdBy', 'approvedBy']);
        });
    }

    public function checkBudgetThreshold(Project $project): ?string
    {
        $utilizationRate = $project->utilization_rate;

        if ($utilizationRate >= $this->thresholds['exceeded']) {
            return 'exceeded';
        }

        if ($utilizationRate >= $this->thresholds['critical']) {
            return 'critical';
        }

        if ($utilizationRate >= $this->thresholds['warning']) {
            return 'warning';
        }

        return null;
    }

    /**
     * Get budget status label based on utilization rate
     */
    private function getBudgetStatus(float $utilizationRate): string
    {
        if ($utilizationRate >= 100) {
            return 'exceeded';
        }
        if ($utilizationRate >= 90) {
            return 'critical';
        }
        if ($utilizationRate >= 75) {
            return 'warning';
        }
        if ($utilizationRate >= 50) {
            return 'on_track';
        }

        return 'under_utilized';
    }

    /**
     * Get variance analysis for budget categories
     */
    private function getVarianceAnalysis(Project $project, array $disbursementsByCategory): array
    {
        $totalBudget = (float) $project->budget;
        $totalSpent = array_sum(array_column($disbursementsByCategory, 'total'));

        // Calculate category percentages
        $categoryPercentages = [];
        foreach ($disbursementsByCategory as $key => $data) {
            $percentage = $totalSpent > 0 ? ($data['total'] / $totalSpent) * 100 : 0;
            $categoryPercentages[$key] = round($percentage, 2);
        }

        return [
            'total_variance' => $totalBudget - $totalSpent,
            'variance_percentage' => $totalBudget > 0 ? round((($totalBudget - $totalSpent) / $totalBudget) * 100, 2) : 0,
            'category_distribution' => $categoryPercentages,
            'largest_expense_category' => $this->getLargestCategory($disbursementsByCategory),
        ];
    }

    /**
     * Get the category with highest spending
     */
    private function getLargestCategory(array $disbursementsByCategory): ?array
    {
        $maxTotal = 0;
        $largestCategory = null;

        foreach ($disbursementsByCategory as $key => $data) {
            if ($data['total'] > $maxTotal) {
                $maxTotal = $data['total'];
                $largestCategory = [
                    'category' => $key,
                    'label' => $data['label'],
                    'total' => $data['total'],
                ];
            }
        }

        return $largestCategory;
    }

    /**
     * Check budget threshold and send notification if needed
     */
    private function checkAndNotifyBudgetThreshold(Project $project): void
    {
        try {
            $threshold = $this->checkBudgetThreshold($project);

            if ($threshold && $project->submitter) {
                // Check if we already sent this threshold notification recently
                $recentNotification = $project->submitter->notifications()
                    ->where('type', BudgetAlertNotification::class)
                    ->where('data->project_id', $project->id)
                    ->where('data->threshold', $threshold)
                    ->where('created_at', '>=', now()->subDay())
                    ->exists();

                if (! $recentNotification) {
                    $project->submitter->notify(
                        new BudgetAlertNotification($project, $threshold, $project->utilization_rate)
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send budget alert notification: '.$e->getMessage());
        }
    }

    /**
     * Create audit log entry
     */
    private function createAuditLog(ProjectDisbursement $disbursement, string $action, ?array $oldValues = null): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "disbursement_{$action}",
                'model_type' => ProjectDisbursement::class,
                'model_id' => $disbursement->id,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => json_encode($disbursement->toArray()),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create audit log for disbursement: '.$e->getMessage());
        }
    }
}
