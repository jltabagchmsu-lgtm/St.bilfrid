<?php

namespace Tests\Unit\Models;

use App\Models\Personnel;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: isLicenseExpired() Method Branch Coverage
     * Path 1: license_status in ['expired', 'inactive', 'suspended', 'revoked'] (case-insensitive) -> true
     * Path 2: license_expiry_date is in past -> true
     * Path 3: license_expiry_date is in future and status is active -> false
     * Path 4: license_status is null and expiry_date is null -> false
     */
    public function test_is_license_expired_branches()
    {
        $expiredStatuses = ['expired', 'EXPIRED', 'inactive', 'Inactive', 'suspended', 'revoked'];
        foreach ($expiredStatuses as $status) {
            $personnel = new Personnel([
                'license_status' => $status,
                'license_expiry_date' => now()->addYears(2),
            ]);
            $this->assertTrue($personnel->isLicenseExpired(), "Failed for license status: {$status}");
        }

        // Past expiry date with active status
        $pastDatePersonnel = new Personnel([
            'license_status' => 'active',
            'license_expiry_date' => now()->subDays(10),
        ]);
        $this->assertTrue($pastDatePersonnel->isLicenseExpired());

        // Future expiry date with active status
        $futureDatePersonnel = new Personnel([
            'license_status' => 'active',
            'license_expiry_date' => now()->addMonths(6),
        ]);
        $this->assertFalse($futureDatePersonnel->isLicenseExpired());

        // Null status and null date
        $nullPersonnel = new Personnel([
            'license_status' => null,
            'license_expiry_date' => null,
        ]);
        $this->assertFalse($nullPersonnel->isLicenseExpired());
    }

    /**
     * White-Box Test: getLicenseStatusBadgeAttribute Branch Coverage
     * Path 1: isLicenseExpired() is true -> 'EXPIRED LICENSE' (#ef4444)
     * Path 2: isLicenseExpired() is false -> 'ACTIVE' (#10b981)
     */
    public function test_license_status_badge_branches()
    {
        $expiredPersonnel = new Personnel([
            'license_status' => 'suspended',
        ]);
        $badgeExpired = $expiredPersonnel->license_status_badge;
        $this->assertEquals('expired', $badgeExpired['status']);
        $this->assertEquals('EXPIRED LICENSE', $badgeExpired['label']);
        $this->assertEquals('#ef4444', $badgeExpired['bg']);

        $activePersonnel = new Personnel([
            'license_status' => 'active',
            'license_expiry_date' => now()->addYear(),
        ]);
        $badgeActive = $activePersonnel->license_status_badge;
        $this->assertEquals('active', $badgeActive['status']);
        $this->assertEquals('ACTIVE', $badgeActive['label']);
        $this->assertEquals('#10b981', $badgeActive['bg']);
    }

    /**
     * White-Box Test: Personnel Relationships (projects with pivot, tasks)
     */
    public function test_personnel_relationships()
    {
        $personnel = Personnel::create([
            'name' => 'Engr. Robert Tan',
            'title' => 'Structural Engineer',
            'email' => 'robert.tan@firm.test',
            'phone' => '+639170001111',
            'license_no' => 'PRC-00998877',
            'specialization' => 'Structural Engineering',
            'license_expiry_date' => now()->addYears(2),
            'license_status' => 'active',
        ]);

        $project = Project::create([
            'project_code' => 'PRJ-PERS-01',
            'title' => 'Seaside Villa',
            'client_name' => 'Dr. Reyes',
            'location' => 'Batangas',
            'land_area_sqm' => 500.0,
            'floor_area_sqm' => 450.0,
            'start_date' => '2026-03-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);

        $personnel->projects()->attach($project->id, [
            'assignment_role' => 'Lead Structural Engineer',
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'task_name' => 'Foundation Beam Inspection',
            'category' => 'Structural',
            'assigned_personnel_id' => $personnel->id,
            'start_date' => '2026-03-05',
            'due_date' => '2026-03-20',
            'status' => 'in_progress',
            'progress' => 50,
        ]);

        $this->assertCount(1, $personnel->projects);
        $this->assertEquals($project->id, $personnel->projects->first()->id);
        $this->assertEquals('Lead Structural Engineer', $personnel->projects->first()->pivot->assignment_role);

        $this->assertCount(1, $personnel->tasks);
        $this->assertEquals($task->id, $personnel->tasks->first()->id);
    }
}
