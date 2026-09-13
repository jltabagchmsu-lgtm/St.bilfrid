<?php

namespace Tests\Unit\Models;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskMaterial;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTaskAndTaskMaterialModelsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: ProjectTask Completion & Status Label/Badge Branch Coverage
     */
    public function test_project_task_status_badge_and_label_branches()
    {
        // 1. Completed
        $t1 = new ProjectTask(['progress' => 100, 'status' => 'not_started']);
        $this->assertTrue($t1->is_completed);
        $this->assertEquals('completed', $t1->status_badge_class);
        $this->assertEquals('Completed', $t1->status_label);

        $t1b = new ProjectTask(['progress' => 50, 'status' => 'completed']);
        $this->assertTrue($t1b->is_completed);
        $this->assertEquals('completed', $t1b->status_badge_class);

        // 2. In Progress
        $t2 = new ProjectTask(['progress' => 50, 'status' => 'not_started']);
        $this->assertFalse($t2->is_completed);
        $this->assertEquals('in_progress', $t2->status_badge_class);
        $this->assertEquals('In Progress', $t2->status_label);

        $t2b = new ProjectTask(['progress' => 10, 'status' => 'in_progress']);
        $this->assertEquals('in_progress', $t2b->status_badge_class);

        // 3. Started / Pending
        $t3 = new ProjectTask(['progress' => 10, 'status' => 'not_started']);
        $this->assertEquals('pending', $t3->status_badge_class);
        $this->assertEquals('Started', $t3->status_label);

        $t3b = new ProjectTask(['progress' => 0, 'status' => 'started']);
        $this->assertEquals('pending', $t3b->status_badge_class);

        // 4. Not started / Neutral
        $t4 = new ProjectTask(['progress' => 0, 'status' => 'not_started']);
        $this->assertEquals('overdue', $t4->status_badge_class);
        $this->assertEquals('Not Started', $t4->status_label);
    }

    /**
     * White-Box Test: ProjectTask Timeline Phase & Badge Color Branch Coverage
     */
    public function test_project_task_timeline_phase_branches()
    {
        $phaseTests = [
            'Phase 1: Mobilization & Substructure' => ['key' => 'phase1', 'color' => '#38bdf8'],
            'Mobilization & Site Clearing' => ['key' => 'phase1', 'color' => '#38bdf8'],
            'Phase 2: Superstructure & Framing' => ['key' => 'phase2', 'color' => '#3b82f6'],
            'Framing & Slab Construction' => ['key' => 'phase2', 'color' => '#3b82f6'],
            'Phase 3: MEP Rough-Ins' => ['key' => 'phase3', 'color' => '#f59e0b'],
            'Utility & Infrastructure' => ['key' => 'phase3', 'color' => '#f59e0b'],
            'Phase 4: Architectural Fit-Out & Finishes' => ['key' => 'phase4', 'color' => '#ec4899'],
            'Finishes & Cabinetry' => ['key' => 'phase4', 'color' => '#ec4899'],
            'Phase 5: Commissioning & Handover' => ['key' => 'phase5', 'color' => '#10b981'],
            'Testing & Handover Inspection' => ['key' => 'phase5', 'color' => '#10b981'],
            'Other Custom Scope' => ['key' => 'phase1', 'color' => '#38bdf8'],
        ];

        foreach ($phaseTests as $phaseString => $expected) {
            $task = new ProjectTask(['timeline_phase' => $phaseString]);
            $this->assertEquals($expected['key'], $task->timeline_phase_key, "Failed key for phase: {$phaseString}");
            $this->assertEquals($expected['color'], $task->timeline_phase_badge_color, "Failed color for phase: {$phaseString}");
        }
    }

    /**
     * White-Box Test: ProjectTask Timeline Window Label Branch Coverage
     */
    public function test_project_task_timeline_window_label_branches()
    {
        $taskWithDates = new ProjectTask([
            'start_date' => '2026-09-01',
            'due_date' => '2026-09-30',
            'timeline_month' => 'Month 1 - 2',
        ]);
        $this->assertEquals('Sep 01 - Sep 30, 2026', $taskWithDates->timeline_window_label);

        $taskWithoutDates = new ProjectTask([
            'start_date' => null,
            'due_date' => null,
            'timeline_month' => 'Month 3 - 4',
        ]);
        $this->assertEquals('Month 3 - 4', $taskWithoutDates->timeline_window_label);

        $taskDefaultFallback = new ProjectTask([
            'start_date' => null,
            'due_date' => null,
            'timeline_month' => null,
        ]);
        $this->assertEquals('Scheduled Window', $taskDefaultFallback->timeline_window_label);
    }

    /**
     * White-Box Test: ProjectTaskMaterial Model Boot Hook Auto-Calculation
     * Path: Saving model automatically calculates total_cost = quantity * unit_cost
     */
    public function test_project_task_material_boot_saving_auto_calculation()
    {
        $project = Project::create([
            'project_code' => 'PRJ-TM-01',
            'title' => 'Task Material Test Project',
            'client_name' => 'Client TM',
            'location' => 'Mandaluyong',
            'land_area_sqm' => 200.0,
            'floor_area_sqm' => 300.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-10-31',
            'status' => 'in_progress',
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'task_name' => 'Concrete Pouring Task',
            'category' => 'Structural',
            'start_date' => '2026-01-10',
            'due_date' => '2026-01-25',
            'status' => 'in_progress',
        ]);

        $tm = ProjectTaskMaterial::create([
            'project_task_id' => $task->id,
            'material_name' => 'Ready-Mix Concrete 3500 PSI',
            'category' => 'Structural',
            'unit' => 'cu.m',
            'unit_cost' => 4350.00,
            'quantity' => 12.5,
            'status' => 'pending',
        ]);

        $this->assertEquals(54375.00, $tm->total_cost); // 12.5 * 4350
        $this->assertInstanceOf(ProjectTask::class, $tm->projectTask);
    }

    /**
     * White-Box Test: ServiceRequest Model Casts & Fillable
     */
    public function test_service_request_model()
    {
        $sr = ServiceRequest::create([
            'request_code' => 'REQ-2026-001',
            'client_name' => 'Dr. Manuel',
            'client_email' => 'manuel@example.test',
            'client_phone' => '+639180002222',
            'service_type' => 'Design-Build Turnkey',
            'land_area_sqm' => 300.5,
            'floor_area_sqm' => 450.75,
            'estimated_cost' => 12500000.00,
            'requested_start_date' => '2026-10-01',
            'status' => 'pending',
            'notes' => 'Modern Mediterranean 2-storey house',
        ]);

        $this->assertIsFloat($sr->land_area_sqm);
        $this->assertEquals(300.5, $sr->land_area_sqm);
        $this->assertIsFloat($sr->floor_area_sqm);
        $this->assertEquals(450.75, $sr->floor_area_sqm);
        $this->assertEquals(12500000.00, $sr->estimated_cost);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $sr->requested_start_date);
    }
}
