<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectDisbursement;
use App\Models\Document;
use App\Models\NewsUpdate;
use Carbon\Carbon;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates realistic audit logs for various models in the system
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];

        $ipAddresses = [
            '192.168.1.' . rand(1, 254),
            '10.0.0.' . rand(1, 254),
            '172.16.0.' . rand(1, 254),
            '203.177.' . rand(1, 254) . '.' . rand(1, 254), // Philippines IP range
        ];

        $totalLogs = 0;

        // Audit logs for Projects
        $this->command->info('Creating audit logs for Projects...');
        $projects = Project::all();
        foreach ($projects as $project) {
            $createdAt = Carbon::parse($project->created_at);

            // Created log
            AuditLog::create([
                'user_id' => $users->random()->id,
                'action' => 'created',
                'model_type' => 'App\Models\Project',
                'model_id' => $project->id,
                'old_values' => null,
                'new_values' => [
                    'title' => $project->title,
                    'status' => 'draft',
                    'budget' => $project->budget,
                ],
                'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'created_at' => $createdAt,
            ]);
            $totalLogs++;

            // 1-3 updates per project
            $updateCount = rand(1, 3);
            for ($i = 0; $i < $updateCount; $i++) {
                $updateDate = $createdAt->copy()->addDays(rand(5, 60));

                $changes = $this->generateProjectChanges();

                AuditLog::create([
                    'user_id' => $users->random()->id,
                    'action' => 'updated',
                    'model_type' => 'App\Models\Project',
                    'model_id' => $project->id,
                    'old_values' => $changes['old'],
                    'new_values' => $changes['new'],
                    'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                    'user_agent' => $userAgents[array_rand($userAgents)],
                    'created_at' => $updateDate,
                ]);
                $totalLogs++;
            }
        }

        // Audit logs for Project Disbursements
        $this->command->info('Creating audit logs for Disbursements...');
        $disbursements = ProjectDisbursement::limit(100)->get();
        foreach ($disbursements as $disbursement) {
            $createdAt = Carbon::parse($disbursement->created_at);

            // Created log
            AuditLog::create([
                'user_id' => $disbursement->created_by ?? $users->random()->id,
                'action' => 'created',
                'model_type' => 'App\Models\ProjectDisbursement',
                'model_id' => $disbursement->id,
                'old_values' => null,
                'new_values' => [
                    'amount' => $disbursement->amount,
                    'category' => $disbursement->category,
                    'status' => 'pending',
                ],
                'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'created_at' => $createdAt,
            ]);
            $totalLogs++;

            // If approved, log the approval
            if ($disbursement->status === 'approved' && $disbursement->approved_at) {
                AuditLog::create([
                    'user_id' => $disbursement->approved_by ?? $users->random()->id,
                    'action' => 'updated',
                    'model_type' => 'App\Models\ProjectDisbursement',
                    'model_id' => $disbursement->id,
                    'old_values' => [
                        'status' => 'pending',
                        'approved_by' => null,
                    ],
                    'new_values' => [
                        'status' => 'approved',
                        'approved_by' => $disbursement->approved_by,
                    ],
                    'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                    'user_agent' => $userAgents[array_rand($userAgents)],
                    'created_at' => $disbursement->approved_at,
                ]);
                $totalLogs++;
            }
        }

        // Audit logs for Documents
        $this->command->info('Creating audit logs for Documents...');
        $documents = Document::limit(50)->get();
        foreach ($documents as $document) {
            $createdAt = Carbon::parse($document->created_at);

            // Created log
            AuditLog::create([
                'user_id' => $document->created_by ?? $users->random()->id,
                'action' => 'created',
                'model_type' => 'App\Models\Document',
                'model_id' => $document->id,
                'old_values' => null,
                'new_values' => [
                    'title' => $document->title,
                    'document_type' => $document->document_type,
                    'is_featured' => false,
                ],
                'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'created_at' => $createdAt,
            ]);
            $totalLogs++;

            // Some documents get updated
            if (rand(1, 100) <= 40) {
                $updateDate = $createdAt->copy()->addDays(rand(1, 30));

                AuditLog::create([
                    'user_id' => $users->random()->id,
                    'action' => 'updated',
                    'model_type' => 'App\Models\Document',
                    'model_id' => $document->id,
                    'old_values' => [
                        'is_featured' => false,
                    ],
                    'new_values' => [
                        'is_featured' => true,
                    ],
                    'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                    'user_agent' => $userAgents[array_rand($userAgents)],
                    'created_at' => $updateDate,
                ]);
                $totalLogs++;
            }
        }

        // Audit logs for News Updates
        $this->command->info('Creating audit logs for News Updates...');
        $newsUpdates = NewsUpdate::limit(30)->get();
        foreach ($newsUpdates as $news) {
            $createdAt = Carbon::parse($news->created_at);

            // Created log
            AuditLog::create([
                'user_id' => $news->author_id ?? $users->random()->id,
                'action' => 'created',
                'model_type' => 'App\Models\NewsUpdate',
                'model_id' => $news->id,
                'old_values' => null,
                'new_values' => [
                    'title' => $news->title,
                    'is_featured' => false,
                    'status' => 'draft',
                ],
                'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'created_at' => $createdAt,
            ]);
            $totalLogs++;

            // Published update
            if ($news->published_at) {
                AuditLog::create([
                    'user_id' => $news->author_id ?? $users->random()->id,
                    'action' => 'updated',
                    'model_type' => 'App\Models\NewsUpdate',
                    'model_id' => $news->id,
                    'old_values' => [
                        'status' => 'draft',
                        'published_at' => null,
                    ],
                    'new_values' => [
                        'status' => 'published',
                        'published_at' => $news->published_at,
                    ],
                    'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                    'user_agent' => $userAgents[array_rand($userAgents)],
                    'created_at' => $news->published_at,
                ]);
                $totalLogs++;
            }
        }

        // User activity logs
        $this->command->info('Creating user activity audit logs...');
        foreach ($users->random(10) as $user) {
            $createdAt = Carbon::parse($user->created_at);

            // User created
            AuditLog::create([
                'user_id' => $users->where('id', '!=', $user->id)->random()->id,
                'action' => 'created',
                'model_type' => 'App\Models\User',
                'model_id' => $user->id,
                'old_values' => null,
                'new_values' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'is_active' => true,
                ],
                'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'created_at' => $createdAt,
            ]);
            $totalLogs++;

            // Profile updates
            if (rand(1, 100) <= 60) {
                $updateDate = $createdAt->copy()->addDays(rand(10, 90));

                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'updated',
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id,
                    'old_values' => [
                        'phone_number' => null,
                    ],
                    'new_values' => [
                        'phone_number' => '+639' . rand(100000000, 999999999),
                    ],
                    'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                    'user_agent' => $userAgents[array_rand($userAgents)],
                    'created_at' => $updateDate,
                ]);
                $totalLogs++;
            }
        }

        $this->command->info("Created {$totalLogs} realistic audit log entries!");
    }

    /**
     * Generate random project changes for audit logs
     */
    private function generateProjectChanges(): array
    {
        $changeTypes = [
            [
                'old' => ['status' => 'On Track'],
                'new' => ['status' => 'Delayed'],
            ],
            [
                'old' => ['budget' => 45000000.00],
                'new' => ['budget' => 48000000.00],
            ],
            [
                'old' => ['description' => 'Original description...'],
                'new' => ['description' => 'Updated description with more details...'],
            ],
            [
                'old' => ['is_public' => false],
                'new' => ['is_public' => true],
            ],
            [
                'old' => ['end_date' => '2025-06-30'],
                'new' => ['end_date' => '2025-12-31'],
            ],
        ];

        return $changeTypes[array_rand($changeTypes)];
    }
}
