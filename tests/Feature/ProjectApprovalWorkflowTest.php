<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\LguSector;
use App\Models\Municipality;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ProjectType;
use App\Models\Province;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * ProjectApprovalWorkflowTest
 *
 * This test suite automates the entire RA 7160-compliant project approval workflow.
 *
 * Workflow Levels:
 * - Level 0: Barangay Development Council Officer (BDC) - Creates and submits project
 * - Level 1: Municipal Planning & Development Officer (MPDO) - Municipal validation
 * - Level 2: Provincial Planning & Development Officer (PPDO) - Provincial review
 * - Level 3: Provincial Governor - Final approval authority
 *
 * Status Flow:
 * draft → pending_barangay → pending_municipal → pending_provincial → pending_governor → approved
 */
class ProjectApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $barangayOfficer;
    private User $municipalOfficer;
    private User $provincialOfficer;
    private User $governor;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed necessary data
        $this->seedRolesAndDepartments();
        $this->seedLocations();
        $this->seedProjectMetadata();
        $this->seedSectors();

        // Create test users for each approval level
        $this->createTestUsers();
    }

    /**
     * Test successful complete approval workflow
     *
     * @test
     */
    public function it_completes_full_approval_workflow_successfully(): void
    {
        // Step 1: Barangay Officer creates and submits project
        Sanctum::actingAs($this->barangayOfficer);

        // Create project
        $projectData = $this->getProjectData();
        $response = $this->postJson('/api/projects', $projectData);

        $response->assertStatus(201);
        $this->project = Project::find($response->json('data.id'));
        $this->assertEquals('draft', $this->project->approval_status);
        $this->assertNotNull($this->project);

        echo "\n✓ Step 1: Barangay Officer created project (Status: draft)\n";

        // Submit for approval
        $response = $this->postJson("/api/projects/{$this->project->id}/submit-for-approval");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project submitted for approval successfully',
            ]);

        $this->project->refresh();
        $this->assertEquals('pending_barangay', $this->project->approval_status);

        echo "✓ Step 2: Barangay Officer submitted project for approval (Status: pending_barangay)\n";

        // Step 2: Barangay Officer approves (moves to municipal)
        // Note: In the current implementation, the barangay officer who submitted
        // cannot approve their own submission. For this test, we'll use the same
        // user, but in production, a different barangay officer would approve.
        $response = $this->postJson("/api/projects/{$this->project->id}/approve", [
            'comments' => 'Barangay Development Council approves this project.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->project->refresh();
        $this->assertEquals('pending_municipal', $this->project->approval_status);

        echo "✓ Step 3: Barangay Officer approved (Status: pending_municipal)\n";

        // Step 3: Municipal Planning Officer approves (moves to provincial)
        Sanctum::actingAs($this->municipalOfficer);

        $response = $this->postJson("/api/projects/{$this->project->id}/approve", [
            'comments' => 'Municipal Planning & Development Office validates and approves.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project approved and moved to next approval level',
            ]);

        $this->project->refresh();
        $this->assertEquals('pending_provincial', $this->project->approval_status);

        echo "✓ Step 4: Municipal Planning Officer approved (Status: pending_provincial)\n";

        // Step 4: Provincial Planning Officer approves (moves to governor)
        Sanctum::actingAs($this->provincialOfficer);

        $response = $this->postJson("/api/projects/{$this->project->id}/approve", [
            'comments' => 'Provincial Planning & Development Office approves for Governor review.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project approved and moved to next approval level',
            ]);

        $this->project->refresh();
        $this->assertEquals('pending_governor', $this->project->approval_status);

        echo "✓ Step 5: Provincial Planning Officer approved (Status: pending_governor)\n";

        // Step 5: Governor gives final approval
        Sanctum::actingAs($this->governor);

        $response = $this->postJson("/api/projects/{$this->project->id}/approve", [
            'comments' => 'Governor grants final approval. Project is now fully approved.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project has been fully approved',
            ]);

        $this->project->refresh();
        $this->assertEquals('approved', $this->project->approval_status);

        echo "✓ Step 6: Governor approved (Status: approved - FINAL)\n";

        // Verify approval history
        $historyResponse = $this->getJson("/api/projects/{$this->project->id}/approval-history");

        $historyResponse->assertStatus(200);
        $history = $historyResponse->json('data.history');

        $this->assertCount(5, $history); // submitted + 4 approvals

        // Verify we have the right actions
        $actions = array_column($history, 'action');
        $this->assertContains('submitted', $actions);
        $this->assertEquals(4, count(array_filter($actions, fn($a) => $a === 'approved')));

        echo "✓ Step 7: Verified approval history (5 entries)\n\n";
        echo "========================================\n";
        echo "✅ COMPLETE APPROVAL WORKFLOW SUCCESSFUL\n";
        echo "========================================\n";
    }

    /**
     * Test rejection at municipal level
     *
     * @test
     */
    public function it_rejects_project_at_municipal_level(): void
    {
        // Create and submit project
        Sanctum::actingAs($this->barangayOfficer);

        $projectData = $this->getProjectData();
        $response = $this->postJson('/api/projects', $projectData);
        $response->assertStatus(201); // Ensure project is created
        $this->project = Project::find($response->json('data.id'));
        $this->assertNotNull($this->project, 'Project should be created');

        // Submit for approval
        $this->postJson("/api/projects/{$this->project->id}/submit-for-approval");

        // Barangay approves
        $this->postJson("/api/projects/{$this->project->id}/approve", [
            'comments' => 'BDC approves.',
        ]);

        $this->project->refresh();
        $this->assertEquals('pending_municipal', $this->project->approval_status);

        echo "\n✓ Project submitted and approved by Barangay (Status: pending_municipal)\n";

        // Municipal officer rejects
        Sanctum::actingAs($this->municipalOfficer);

        $response = $this->postJson("/api/projects/{$this->project->id}/reject", [
            'comments' => 'Project does not meet municipal requirements.',
            'reason' => 'Insufficient budget justification',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project has been rejected',
            ]);

        $this->project->refresh();
        $this->assertEquals('rejected', $this->project->approval_status);

        echo "✓ Municipal Officer rejected project (Status: rejected)\n";

        // Verify approval history
        $historyResponse = $this->getJson("/api/projects/{$this->project->id}/approval-history");
        $history = $historyResponse->json('data.history');

        $this->assertCount(3, $history); // submitted + barangay approved + municipal rejected

        // Verify we have a rejection at municipal level
        $actions = array_column($history, 'action');
        $this->assertContains('rejected', $actions);
        $this->assertContains('submitted', $actions);
        $this->assertContains('approved', $actions);

        // Find and verify the rejection entry
        $rejectionEntry = collect($history)->firstWhere('action', 'rejected');
        $this->assertNotNull($rejectionEntry);
        $this->assertEquals('municipal', $rejectionEntry['level']);

        echo "✓ Verified rejection in approval history\n\n";
        echo "========================================\n";
        echo "✅ REJECTION WORKFLOW SUCCESSFUL\n";
        echo "========================================\n";
    }

    /**
     * Test unauthorized approval attempt
     *
     * @test
     */
    public function it_prevents_unauthorized_approval(): void
    {
        // Create and submit project
        Sanctum::actingAs($this->barangayOfficer);

        $projectData = $this->getProjectData();
        $response = $this->postJson('/api/projects', $projectData);
        $response->assertStatus(201); // Ensure project is created
        $this->project = Project::find($response->json('data.id'));
        $this->assertNotNull($this->project, 'Project should be created');

        $this->postJson("/api/projects/{$this->project->id}/submit-for-approval");

        $this->project->refresh();
        $this->assertEquals('pending_barangay', $this->project->approval_status);

        echo "\n✓ Project submitted (Status: pending_barangay)\n";

        // Try to approve with wrong role (Municipal officer cannot approve barangay level)
        Sanctum::actingAs($this->municipalOfficer);

        $response = $this->postJson("/api/projects/{$this->project->id}/approve", [
            'comments' => 'Trying to skip approval level.',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to approve this project at the current level',
            ]);

        $this->project->refresh();
        $this->assertEquals('pending_barangay', $this->project->approval_status);

        echo "✓ Prevented unauthorized approval (Municipal cannot approve Barangay level)\n\n";
        echo "========================================\n";
        echo "✅ AUTHORIZATION CHECK SUCCESSFUL\n";
        echo "========================================\n";
    }

    /**
     * Test request changes workflow
     *
     * @test
     */
    public function it_requests_changes_at_provincial_level(): void
    {
        // Create and submit project
        Sanctum::actingAs($this->barangayOfficer);

        $projectData = $this->getProjectData();
        $response = $this->postJson('/api/projects', $projectData);
        $response->assertStatus(201); // Ensure project is created
        $this->project = Project::find($response->json('data.id'));
        $this->assertNotNull($this->project, 'Project should be created');

        $this->postJson("/api/projects/{$this->project->id}/submit-for-approval");
        $this->postJson("/api/projects/{$this->project->id}/approve");

        // Municipal approves
        Sanctum::actingAs($this->municipalOfficer);
        $this->postJson("/api/projects/{$this->project->id}/approve");

        $this->project->refresh();
        $this->assertEquals('pending_provincial', $this->project->approval_status);

        echo "\n✓ Project at provincial level (Status: pending_provincial)\n";

        // Provincial officer requests changes
        Sanctum::actingAs($this->provincialOfficer);

        $response = $this->postJson("/api/projects/{$this->project->id}/request-changes", [
            'comments' => 'Please provide more detailed budget breakdown and timeline.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Changes have been requested for this project',
            ]);

        $this->project->refresh();
        $this->assertEquals('draft', $this->project->approval_status); // Changes requested sends back to draft

        echo "✓ Provincial Officer requested changes (Status: draft - sent back for revisions)\n\n";
        echo "========================================\n";
        echo "✅ REQUEST CHANGES WORKFLOW SUCCESSFUL\n";
        echo "========================================\n";
    }

    /**
     * Test RA 7160 territorial jurisdiction - MPDO cannot approve projects from other municipalities
     *
     * @test
     */
    public function it_prevents_cross_municipal_approval(): void
    {
        // Create two municipalities in the same province
        $province = Province::first();
        $municipalityA = Municipality::firstOrCreate(
            ['psgc_code' => '160203000'],
            [
                'province_id' => $province->id,
                'name' => 'Bunawan',
                'code' => 'BUNA',
                'is_city' => false,
            ]
        );
        $municipalityB = Municipality::firstOrCreate(
            ['psgc_code' => '160204000'],
            [
                'province_id' => $province->id,
                'name' => 'Bayugan',
                'code' => 'BAYU',
                'is_city' => false,
            ]
        );

        $mpdoRole = Role::firstOrCreate(
            ['name' => 'Municipal Planning Officer (MPDO)'],
            ['description' => 'MPDO']
        );

        $bdcRole = Role::firstOrCreate(
            ['name' => 'Barangay Development Council Officer'],
            ['description' => 'BDC Officer']
        );

        // Create BDC officer for Municipality B
        $bdcB = User::create([
            'full_name' => 'BDC Municipality B',
            'username' => 'test_bdc_b',
            'email' => 'bdc_b@test.com',
            'password' => Hash::make('password'),
            'role_id' => $bdcRole->id,
            'department_id' => $this->barangayOfficer->department_id,
            'municipality_id' => $municipalityB->id, // Assigned to Municipality B
        ]);

        // Create MPDO for Municipality A
        $mpdoA = User::create([
            'full_name' => 'MPDO Municipality A',
            'username' => 'test_mpdo_a',
            'email' => 'mpdo_a@test.com',
            'password' => Hash::make('password'),
            'role_id' => $mpdoRole->id,
            'department_id' => $this->municipalOfficer->department_id,
            'municipality_id' => $municipalityA->id, // Assigned to Municipality A
        ]);

        // Reload user with relationships to ensure municipality_id is set
        $bdcB = $bdcB->fresh(['role', 'department', 'municipality']);

        // Create project in Municipality B
        Sanctum::actingAs($bdcB);
        $projectData = $this->getProjectData('Project in Municipality B');
        $projectData['municipality_id'] = $municipalityB->id; // Project is in Municipality B
        $response = $this->postJson('/api/projects', $projectData);
        $response->assertStatus(201);
        $project = Project::find($response->json('data.id'));

        // Debug: Check if municipality_id was set correctly
        $project = $project->fresh();
        echo "\n[DEBUG] Project municipality_id: " . ($project->municipality_id ?? 'NULL') . "\n";
        echo "[DEBUG] BDC user municipality_id: " . ($bdcB->municipality_id ?? 'NULL') . "\n";
        echo "[DEBUG] Municipality B ID: " . $municipalityB->id . "\n";

        // Submit project for approval
        $this->postJson("/api/projects/{$project->id}/submit-for-approval");

        // Barangay officer from Municipality B approves (moves to municipal level)
        $approvalResponse = $this->postJson("/api/projects/{$project->id}/approve", [
            'comments' => 'BDC from Municipality B approves.',
        ]);

        // Check if approval succeeded
        if ($approvalResponse->status() !== 200) {
            echo "\n❌ BDC approval failed: " . $approvalResponse->json('message') . "\n";
            echo "Response: " . json_encode($approvalResponse->json(), JSON_PRETTY_PRINT) . "\n";
        }

        $approvalResponse->assertStatus(200);

        $project->refresh();
        $this->assertEquals('pending_municipal', $project->approval_status);

        echo "\n✓ Project from Municipality B is pending_municipal\n";

        // CRITICAL TEST: MPDO from Municipality A tries to approve project from Municipality B
        Sanctum::actingAs($mpdoA);

        $response = $this->postJson("/api/projects/{$project->id}/approve", [
            'comments' => 'Attempting cross-municipal approval (should FAIL).',
        ]);

        // Should be FORBIDDEN (403) - RA 7160 territorial jurisdiction violation
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to approve this project at the current level',
            ]);

        // Verify project status has NOT changed
        $project->refresh();
        $this->assertEquals('pending_municipal', $project->approval_status);

        echo "✓ Cross-municipal approval BLOCKED (RA 7160 compliance)\n";

        // Verify MPDO A cannot see this project in their pending approvals
        $response = $this->getJson('/api/projects/pending-approval');
        $response->assertStatus(200);
        $projects = $response->json('data');

        // MPDO A should see 0 projects (project is in Municipality B, not A)
        $this->assertEmpty($projects, 'MPDO from Municipality A should not see projects from Municipality B');

        echo "✓ MPDO A cannot see projects from Municipality B in pending approvals\n";

        // Now verify MPDO from Municipality B CAN approve the project
        $mpdoB = User::create([
            'full_name' => 'MPDO Municipality B',
            'username' => 'test_mpdo_b',
            'email' => 'mpdo_b@test.com',
            'password' => Hash::make('password'),
            'role_id' => $mpdoRole->id,
            'department_id' => $this->municipalOfficer->department_id,
            'municipality_id' => $municipalityB->id, // Assigned to Municipality B
        ]);

        Sanctum::actingAs($mpdoB);

        // MPDO B should see this project in pending approvals
        $response = $this->getJson('/api/projects/pending-approval');
        $response->assertStatus(200);
        $projects = $response->json('data');
        $this->assertNotEmpty($projects, 'MPDO from Municipality B should see projects from Municipality B');
        $this->assertEquals($project->id, $projects[0]['id']);

        echo "✓ MPDO B can see projects from Municipality B in pending approvals\n";

        // MPDO B should be able to approve
        $response = $this->postJson("/api/projects/{$project->id}/approve", [
            'comments' => 'MPDO from correct municipality approves.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project approved and moved to next approval level',
            ]);

        $project->refresh();
        $this->assertEquals('pending_provincial', $project->approval_status);

        echo "✓ MPDO B successfully approved project from their municipality\n\n";
        echo "========================================\n";
        echo "✅ RA 7160 TERRITORIAL JURISDICTION TEST PASSED\n";
        echo "========================================\n";
    }

    /**
     * Test pending approvals retrieval
     *
     * @test
     */
    public function it_retrieves_pending_approvals_for_each_level(): void
    {
        // Create multiple projects at different levels
        Sanctum::actingAs($this->barangayOfficer);

        // Project 1: At barangay level
        $response1 = $this->postJson('/api/projects', $this->getProjectData('Project 1'));
        $project1 = Project::find($response1->json('data.id'));
        $this->postJson("/api/projects/{$project1->id}/submit-for-approval");

        // Project 2: At municipal level
        $response2 = $this->postJson('/api/projects', $this->getProjectData('Project 2'));
        $project2 = Project::find($response2->json('data.id'));
        $this->postJson("/api/projects/{$project2->id}/submit-for-approval");
        $this->postJson("/api/projects/{$project2->id}/approve");

        // Project 3: At provincial level
        $response3 = $this->postJson('/api/projects', $this->getProjectData('Project 3'));
        $project3 = Project::find($response3->json('data.id'));
        $this->postJson("/api/projects/{$project3->id}/submit-for-approval");
        $this->postJson("/api/projects/{$project3->id}/approve");

        Sanctum::actingAs($this->municipalOfficer);
        $this->postJson("/api/projects/{$project3->id}/approve");

        echo "\n✓ Created 3 projects at different approval levels\n";

        // Check barangay officer's pending approvals
        Sanctum::actingAs($this->barangayOfficer);
        $response = $this->getJson('/api/projects/pending-approval');
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total')); // Only project1
        echo "✓ Barangay Officer: 1 pending approval\n";

        // Check municipal officer's pending approvals
        Sanctum::actingAs($this->municipalOfficer);
        $response = $this->getJson('/api/projects/pending-approval');
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total')); // Only project2
        echo "✓ Municipal Officer: 1 pending approval\n";

        // Check provincial officer's pending approvals
        Sanctum::actingAs($this->provincialOfficer);
        $response = $this->getJson('/api/projects/pending-approval');
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total')); // Only project3
        echo "✓ Provincial Officer: 1 pending approval\n\n";

        echo "========================================\n";
        echo "✅ PENDING APPROVALS RETRIEVAL SUCCESSFUL\n";
        echo "========================================\n";
    }

    // ========================================
    // Helper Methods
    // ========================================

    private function seedRolesAndDepartments(): void
    {
        // Create roles
        Role::firstOrCreate(
            ['name' => 'Barangay Development Council Officer'],
            ['description' => 'BDC Officer']
        );
        Role::firstOrCreate(
            ['name' => 'Municipal Planning Officer (MPDO)'],
            ['description' => 'MPDO']
        );
        Role::firstOrCreate(
            ['name' => 'Provincial Planning Officer (PPDO)'],
            ['description' => 'PPDO']
        );
        Role::firstOrCreate(
            ['name' => 'Provincial Governor'],
            ['description' => 'Governor']
        );

        // Create departments
        Department::firstOrCreate(
            ['name' => 'Field Operations Division'],
            ['description' => 'Field operations']
        );

        Department::firstOrCreate(
            ['name' => 'Planning, Monitoring and Evaluation Division'],
            ['description' => 'Planning and monitoring']
        );

        Department::firstOrCreate(
            ['name' => 'Office of the Regional Executive Director'],
            ['description' => 'Executive office']
        );
    }

    private function seedLocations(): void
    {
        $region = Region::firstOrCreate(
            ['psgc_code' => '160000000'],
            [
                'name' => 'CARAGA',
                'code' => 'XIII',
            ]
        );

        $province = Province::firstOrCreate(
            ['psgc_code' => '160200000'],
            [
                'region_id' => $region->id,
                'name' => 'Agusan del Sur',
                'code' => 'AGS',
            ]
        );

        Municipality::firstOrCreate(
            ['psgc_code' => '160202000'],
            [
                'province_id' => $province->id,
                'name' => 'Prosperidad',
                'code' => 'PROS',
                'is_city' => false,
            ]
        );
    }

    private function seedProjectMetadata(): void
    {
        ProjectType::firstOrCreate(
            ['name' => 'Infrastructure'],
            ['description' => 'Infrastructure projects']
        );

        ProjectStatus::firstOrCreate(
            ['name' => 'Planning'],
            ['description' => 'In planning phase']
        );
    }

    private function seedSectors(): void
    {
        LguSector::firstOrCreate(
            ['code' => 'ES'],
            [
                'name' => 'Economic Services',
                'description' => 'Agriculture, Tourism, Trade & Industry',
                'budget' => 5000000.00,
            ]
        );
    }

    private function createTestUsers(): void
    {
        $roles = Role::all()->keyBy('name');
        $departments = Department::all()->keyBy('name');

        // Barangay Development Council Officer (Level 0)
        $this->barangayOfficer = User::create([
            'full_name' => 'Test Barangay Officer',
            'username' => 'test_bdc',
            'email' => 'bdc@test.com',
            'password' => Hash::make('password'),
            'role_id' => $roles['Barangay Development Council Officer']->id,
            'department_id' => $departments['Field Operations Division']->id,
        ]);

        // Municipal Planning Officer (Level 1)
        $this->municipalOfficer = User::create([
            'full_name' => 'Test Municipal Officer',
            'username' => 'test_mpdo',
            'email' => 'mpdo@test.com',
            'password' => Hash::make('password'),
            'role_id' => $roles['Municipal Planning Officer (MPDO)']->id,
            'department_id' => $departments['Planning, Monitoring and Evaluation Division']->id,
        ]);

        // Provincial Planning Officer (Level 2)
        $this->provincialOfficer = User::create([
            'full_name' => 'Test Provincial Officer',
            'username' => 'test_ppdo',
            'email' => 'ppdo@test.com',
            'password' => Hash::make('password'),
            'role_id' => $roles['Provincial Planning Officer (PPDO)']->id,
            'department_id' => $departments['Planning, Monitoring and Evaluation Division']->id,
        ]);

        // Provincial Governor (Level 3 - Final Authority)
        $this->governor = User::create([
            'full_name' => 'Test Governor',
            'username' => 'test_governor',
            'email' => 'governor@test.com',
            'password' => Hash::make('password'),
            'role_id' => $roles['Provincial Governor']->id,
            'department_id' => $departments['Office of the Regional Executive Director']->id,
        ]);
    }

    private function getProjectData(string $title = 'Test Agricultural Project'): array
    {
        $municipality = Municipality::first();
        $province = Province::first();
        $projectType = ProjectType::first();
        $projectStatus = ProjectStatus::first();
        $sector = LguSector::first();
        $department = Department::first();

        return [
            'title' => $title,
            'description' => 'A comprehensive agricultural development project for CARAGA region.',
            'department_id' => $department->id,
            'sector_id' => $sector->id,
            'municipality_id' => $municipality->id,
            'province_id' => $province->id,
            'barangay' => 'Poblacion',
            'project_type_id' => $projectType->id,
            'project_status_id' => $projectStatus->id,
            'budget' => 500000.00,
            'start_date' => '2026-02-01',
            'end_date' => '2026-12-31',
            'location_lat' => 8.5988,
            'location_lng' => 125.9756,
            'is_public' => true,
        ];
    }
}
