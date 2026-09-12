<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Personnel;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class BlackBoxTestCasesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear rate limiter keys before tests
        RateLimiter::clear(Str::transliterate('admin@newconstuc.firm|127.0.0.1'));
    }

    /**
     * TC-A010: Rate limiting on multiple consecutive failed login attempts.
     */
    public function test_tc_a010_rate_limiting_on_multiple_consecutive_failed_logins(): void
    {
        $email = 'admin@newconstuc.firm';

        // Ensure user exists
        if (!User::where('email', $email)->exists()) {
            User::factory()->create([
                'email' => $email,
                'password' => bcrypt('AdminMaster2026!'),
                'role' => 'admin',
            ]);
        }

        // 1. Submit 5 consecutive failed login attempts
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'WrongPassword123!',
            ]);
            $response->assertSessionHasErrors('email');
            $errors = session('errors')->get('email');
            $this->assertStringContainsString('Invalid credentials', $errors[0]);
        }

        // 2. The 6th attempt must be throttled by RateLimiter with countdown message
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'WrongPassword123!',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = session('errors')->get('email');
        $this->assertTrue(
            str_contains($errors[0], 'Too many login attempts') || str_contains($errors[0], 'seconds'),
            'Expected lockout/countdown message, got: ' . $errors[0]
        );

        // 3. Clear rate limiter and verify legitimate login passes
        $throttleKey = Str::transliterate(Str::lower($email) . '|127.0.0.1');
        RateLimiter::clear($throttleKey);

        $validResponse = $this->post('/login', [
            'email' => $email,
            'password' => 'AdminMaster2026!',
        ]);
        $validResponse->assertRedirect(route('dashboard'));
    }

    /**
     * TC-A023: Project deletion with incorrect confirmation text.
     */
    public function test_tc_a023_project_deletion_rejects_incorrect_confirmation_text(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin Tester',
                'email' => 'admin_test@firm.com',
                'password' => bcrypt('secret123'),
                'role' => 'admin',
            ]);
        }

        // Create a test project with child task
        $project = Project::create([
            'project_code' => 'PRJ-TEST-DEL-' . time(),
            'title' => 'Test Project For Deletion',
            'client_name' => 'Acme Test Corp',
            'project_type' => 'Commercial Construction',
            'land_area_sqm' => 500.00,
            'floor_area_sqm' => 350.00,
            'status' => 'in_progress',
            'contract_budget' => 500000.00,
            'spent_budget' => 0.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'task_name' => 'Initial Site Survey',
            'category' => 'General',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => 'not_started',
        ]);

        $invalidConfirmations = ['remove', 'cancel', 'delete', '', 'Delete', 'DELETE123'];

        foreach ($invalidConfirmations as $badText) {
            $response = $this->actingAs($admin)->post('/projects/' . $project->id . '/delete', [
                'confirmation' => $badText,
            ]);

            // Must reject and not delete
            $response->assertSessionHasErrors('confirmation');
            $this->assertDatabaseHas('projects', ['id' => $project->id]);
            $this->assertDatabaseHas('project_tasks', ['id' => $task->id]);
        }

        // Testing direct DELETE request without exact DELETE confirmation
        $response = $this->actingAs($admin)->delete('/projects/' . $project->id, [
            'confirmation' => 'remove',
        ]);
        $response->assertSessionHasErrors('confirmation');
        $this->assertDatabaseHas('projects', ['id' => $project->id]);

        // Now delete with exact confirmation text "DELETE"
        $validDelete = $this->actingAs($admin)->delete('/projects/' . $project->id, [
            'confirmation' => 'DELETE',
        ]);
        $validDelete->assertRedirect();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_tasks', ['id' => $task->id]);
    }

    /**
     * TC-A031: Assign personnel without active license or qualification.
     */
    public function test_tc_a031_personnel_assignment_displays_advisory_warning_for_expired_license(): void
    {
        $admin = User::where('role', 'admin')->first();

        $project = Project::create([
            'project_code' => 'PRJ-LIC-TEST-' . time(),
            'title' => 'License Test Project',
            'client_name' => 'Validation Corp',
            'project_type' => 'Commercial Construction',
            'land_area_sqm' => 450.00,
            'floor_area_sqm' => 300.00,
            'status' => 'in_progress',
            'contract_budget' => 800000.00,
            'spent_budget' => 0.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
        ]);

        // 1. Expired engineer
        $expiredPersonnel = Personnel::create([
            'name' => 'Engr. Expired License Test',
            'title' => 'Senior Structural Engineer',
            'email' => 'expired_test_' . time() . '@newconstuc.firm',
            'phone' => '+63 900 111 2222',
            'license_no' => 'EXP-201801',
            'license_expiry_date' => now()->subMonths(12)->toDateString(),
            'license_status' => 'expired',
            'specialization' => 'Concrete Structures',
        ]);

        $this->assertTrue($expiredPersonnel->isLicenseExpired());

        // 2. Active engineer
        $activePersonnel = Personnel::create([
            'name' => 'Engr. Active License Test',
            'title' => 'Registered Civil Engineer',
            'email' => 'active_test_' . time() . '@newconstuc.firm',
            'phone' => '+63 900 333 4444',
            'license_no' => 'ACT-202901',
            'license_expiry_date' => now()->addYears(3)->toDateString(),
            'license_status' => 'active',
            'specialization' => 'Project Management',
        ]);

        $this->assertFalse($activePersonnel->isLicenseExpired());

        // 3. Assign expired engineer: assignment succeeds, but flashes advisory warning
        $responseExpired = $this->actingAs($admin)->post('/projects/' . $project->id . '/assign-personnel', [
            'personnel_id' => $expiredPersonnel->id,
            'assignment_role' => 'Structural Consultant',
        ]);

        $responseExpired->assertSessionHas('warning');
        $this->assertStringContainsString('Advisory Warning', session('warning'));
        $this->assertStringContainsString('expired or inactive', session('warning'));

        // Verify assignment still happened
        $this->assertDatabaseHas('project_personnel', [
            'project_id' => $project->id,
            'personnel_id' => $expiredPersonnel->id,
            'assignment_role' => 'Structural Consultant',
        ]);

        // 4. Assign active engineer: assignment succeeds with NO warning
        $responseActive = $this->actingAs($admin)->post('/projects/' . $project->id . '/assign-personnel', [
            'personnel_id' => $activePersonnel->id,
            'assignment_role' => 'Lead Site Engineer',
        ]);

        $responseActive->assertSessionHas('success');
        $responseActive->assertSessionMissing('warning');

        $this->assertDatabaseHas('project_personnel', [
            'project_id' => $project->id,
            'personnel_id' => $activePersonnel->id,
            'assignment_role' => 'Lead Site Engineer',
        ]);
    }

    /**
     * TC-A072: Duplicate Official Receipt Number rejection.
     */
    public function test_tc_a072_duplicate_official_receipt_number_is_rejected(): void
    {
        $admin = User::where('role', 'admin')->first();

        $project = Project::first() ?? Project::create([
            'project_code' => 'PRJ-PAY-TEST-' . time(),
            'title' => 'Payment Test Project',
            'client_name' => 'Payee Client Ltd',
            'status' => 'in_progress',
            'current_phase' => 'Finishing',
            'start_date' => now(),
            'target_completion_date' => now()->addMonths(2),
            'contract_amount' => 1200000.00,
        ]);

        $uniqueOr = 'OR-TEST-' . time() . '-8899';

        // 1. Create first payment with official_receipt_no
        $firstResponse = $this->actingAs($admin)->post('/payments/store', [
            'project_id' => $project->id,
            'amount' => 50000.00,
            'payment_date' => now()->toDateString(),
            'payment_stage' => 'Downpayment',
            'payment_method' => 'bank_transfer',
            'status' => 'paid',
            'official_receipt_no' => $uniqueOr,
            'payer_name' => 'Payee Client Ltd',
        ]);

        $firstResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('payments', [
            'project_id' => $project->id,
            'official_receipt_no' => $uniqueOr,
        ]);

        // 2. Attempt creating duplicate payment with same OR number
        $duplicateResponse = $this->actingAs($admin)->post('/payments/store', [
            'project_id' => $project->id,
            'amount' => 25000.00,
            'payment_date' => now()->toDateString(),
            'payment_stage' => 'Milestone 1',
            'payment_method' => 'cash',
            'status' => 'paid',
            'official_receipt_no' => $uniqueOr,
            'payer_name' => 'Payee Client Ltd',
        ]);

        $duplicateResponse->assertSessionHasErrors('official_receipt_no');
        $errorMsg = session('errors')->get('official_receipt_no')[0];
        $this->assertStringContainsString('already been recorded', $errorMsg);

        // Verify only 1 payment exists with this OR number
        $count = Payment::where('official_receipt_no', $uniqueOr)->count();
        $this->assertEquals(1, $count);

        // 3. Verify blank OR number auto-generates a unique candidate without error
        $blankOrResponse = $this->actingAs($admin)->post('/payments/store', [
            'project_id' => $project->id,
            'amount' => 15000.00,
            'payment_date' => now()->toDateString(),
            'payment_stage' => 'Milestone 2',
            'payment_method' => 'check',
            'status' => 'paid',
            'official_receipt_no' => '',
            'payer_name' => 'Payee Client Ltd',
        ]);

        $blankOrResponse->assertSessionHasNoErrors();
    }
}
