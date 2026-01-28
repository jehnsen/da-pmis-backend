<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectDisbursement;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class ProjectDisbursementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates realistic project disbursements for CARAGA agricultural projects
     */
    public function run(): void
    {
        $projects = Project::all();
        $users = User::all();

        if ($projects->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No projects or users found. Please run required seeders first.');
            return;
        }

        $categories = [
            'equipment' => ['weight' => 25, 'vendors' => ['AgriMachinery Philippines', 'Farm Equipment Supplies Inc.', 'Mindanao Agricultural Tools']],
            'labor' => ['weight' => 20, 'vendors' => ['CARAGA Labor Cooperative', 'Agricultural Workers Association', 'Local Farm Workers Group']],
            'materials' => ['weight' => 20, 'vendors' => ['BuildMart Butuan', 'Construction Materials Supply', 'Agusan Hardware']],
            'supplies' => ['weight' => 15, 'vendors' => ['Agricultural Supplies Center', 'Farm Inputs Store', 'Crop Protection Supplies']],
            'services' => ['weight' => 10, 'vendors' => ['Consulting Services Group', 'Technical Advisory Services', 'Agricultural Consultants']],
            'travel' => ['weight' => 5, 'vendors' => ['N/A - Employee Travel', 'Project Monitoring Team', 'Field Inspection Team']],
            'training' => ['weight' => 3, 'vendors' => ['Training Services Provider', 'Agricultural Training Institute', 'Capacity Building Consultants']],
            'utilities' => ['weight' => 1, 'vendors' => ['BUSECO', 'Water District', 'Telecom Services']],
            'maintenance' => ['weight' => 1, 'vendors' => ['Maintenance Services', 'Equipment Repair Shop', 'Facility Maintenance']],
        ];

        $descriptions = [
            'equipment' => [
                'Procurement of 10 units hand tractors for farmer beneficiaries',
                'Purchase of rice harvesting equipment and threshers',
                'Acquisition of irrigation pumps and water distribution systems',
                'Delivery of livestock housing and feeding equipment',
                'Purchase of fish cages and aquaculture equipment',
            ],
            'labor' => [
                'Payment for construction workers - Site preparation phase',
                'Wages for seasonal farm workers during planting season',
                'Labor costs for infrastructure construction activities',
                'Payment for technical staff and field coordinators',
                'Compensation for harvest and post-harvest workers',
            ],
            'materials' => [
                'Purchase of cement, steel bars, and construction materials',
                'Procurement of lumber and roofing materials for facilities',
                'Acquisition of pipes, fittings, and plumbing supplies',
                'Purchase of electrical wiring and installation materials',
                'Delivery of packaging and storage materials',
            ],
            'supplies' => [
                'Purchase of certified seeds and planting materials',
                'Procurement of fertilizers and soil amendments',
                'Acquisition of pesticides and crop protection products',
                'Purchase of veterinary medicines and vaccines',
                'Delivery of feeds and supplements for livestock',
            ],
            'services' => [
                'Professional fees for engineering design services',
                'Consultant fees for technical advisory support',
                'Third-party laboratory testing and analysis fees',
                'Legal and documentation services',
                'Monitoring and evaluation consultancy services',
            ],
            'travel' => [
                'Travel expenses for project monitoring visits',
                'Transportation costs for beneficiary training events',
                'Fuel and vehicle maintenance for field operations',
                'Accommodation during regional coordination meetings',
                'Per diem for technical team field visits',
            ],
            'training' => [
                'Training materials and supplies for Farmer Field School',
                'Honorarium for resource persons and trainers',
                'Venue rental and meals for capacity building workshops',
                'Development and printing of training manuals',
                'Certification costs for trained beneficiaries',
            ],
            'utilities' => [
                'Electricity bills for project facilities',
                'Water utility charges for irrigation and operations',
                'Internet and communication services',
                'Waste management and sanitation services',
            ],
            'maintenance' => [
                'Regular maintenance of farm machinery',
                'Repair of damaged equipment and facilities',
                'Preventive maintenance of irrigation systems',
                'Upkeep of demonstration farms and facilities',
            ],
        ];

        $totalDisbursements = 0;

        foreach ($projects as $project) {
            $projectStart = Carbon::parse($project->start_date);
            $projectEnd = Carbon::parse($project->end_date);
            $projectDuration = $projectStart->diffInMonths($projectEnd);

            // Calculate number of disbursements (1-3 per month on average)
            $disbursementCount = rand($projectDuration, $projectDuration * 3);
            $disbursementCount = min($disbursementCount, 50); // Cap at 50

            $totalDisbursed = 0;

            for ($i = 0; $i < $disbursementCount; $i++) {
                // Select category based on weighted distribution
                $category = $this->selectWeightedCategory($categories);
                $categoryData = $categories[$category];

                // Calculate amount based on project budget and category
                $remainingBudget = $project->budget - $totalDisbursed;
                $categoryPercentage = $categoryData['weight'] / 100;

                // Disbursement between 1-10% of category allocation
                $maxAmount = ($project->budget * $categoryPercentage) * 0.1;
                $minAmount = ($project->budget * $categoryPercentage) * 0.01;
                $amount = rand($minAmount * 100, $maxAmount * 100) / 100;

                // Ensure we don't exceed budget
                $amount = min($amount, $remainingBudget * 0.95);

                if ($amount < 1000) continue; // Skip very small disbursements

                // Generate random disbursement date within project duration
                $disbursementDate = $projectStart->copy()->addDays(rand(0, max(1, $projectStart->diffInDays(min($projectEnd, now())))));

                // Status: 80% completed, 15% pending, 5% cancelled
                $statusRand = rand(1, 100);
                if ($statusRand <= 80) {
                    $status = 'completed';
                    $approvedBy = $users->random()->id;
                    $approvedAt = $disbursementDate->copy()->addDays(rand(1, 5));
                } elseif ($statusRand <= 95) {
                    $status = 'pending';
                    $approvedBy = null;
                    $approvedAt = null;
                } else {
                    $status = 'cancelled';
                    $approvedBy = $users->random()->id;
                    $approvedAt = $disbursementDate->copy()->addDays(rand(1, 3));
                }

                $description = $descriptions[$category][array_rand($descriptions[$category])];
                $vendor = $categoryData['vendors'][array_rand($categoryData['vendors'])];

                ProjectDisbursement::create([
                    'project_id' => $project->id,
                    'amount' => $amount,
                    'disbursement_date' => $disbursementDate,
                    'category' => $category,
                    'description' => $description,
                    'reference_number' => $this->generateReferenceNumber($project->id, $disbursementDate),
                    'vendor_name' => $vendor,
                    'receipt_number' => 'RCP-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'notes' => $this->generateNotes($category, $status),
                    'approved_by' => $approvedBy,
                    'approved_at' => $approvedAt,
                    'created_by' => $users->random()->id,
                    'status' => $status,
                    'created_at' => $disbursementDate->copy()->subDays(rand(1, 5)),
                    'updated_at' => $approvedAt ?? now(),
                ]);

                if ($status === 'completed') {
                    $totalDisbursed += $amount;
                }

                $totalDisbursements++;
            }
        }

        $this->command->info("Created {$totalDisbursements} realistic project disbursements!");
    }

    /**
     * Select a category based on weighted distribution
     */
    private function selectWeightedCategory(array $categories): string
    {
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($categories as $category => $data) {
            $cumulative += $data['weight'];
            if ($rand <= $cumulative) {
                return $category;
            }
        }

        return 'equipment'; // fallback
    }

    /**
     * Generate a reference number for the disbursement
     */
    private function generateReferenceNumber(int $projectId, Carbon $date): string
    {
        return sprintf(
            'DISB-%04d-%s-%04d',
            $projectId,
            $date->format('Ym'),
            rand(1000, 9999)
        );
    }

    /**
     * Generate notes based on category and status
     */
    private function generateNotes(?string $category, string $status): ?string
    {
        $notes = [
            'completed' => [
                'Disbursement approved and processed according to project guidelines.',
                'All required documents submitted and verified. Payment released.',
                'Compliant with disbursement procedures. Approved for payment.',
                'Supporting documents complete. Processed for release.',
            ],
            'pending' => [
                'Awaiting approval from Regional Director.',
                'Under review by finance department. Supporting documents being verified.',
                'Pending submission of additional documentation.',
                'For approval by Project Manager.',
            ],
            'cancelled' => [
                'Cancelled due to procurement issues.',
                'Vendor failed to deliver within specified timeframe.',
                'Project plan revised, disbursement no longer needed.',
                'Replaced by alternative procurement approach.',
            ],
        ];

        $statusNotes = $notes[$status] ?? $notes['completed'];
        return $statusNotes[array_rand($statusNotes)];
    }
}
