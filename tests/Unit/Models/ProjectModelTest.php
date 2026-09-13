<?php

namespace Tests\Unit\Models;

use App\Models\Material;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectMaterial;
use App\Models\ProjectMaterialTransfer;
use App\Models\ProjectScopeItem;
use App\Models\ProjectTask;
use App\Models\ProjectTaskMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: Project Weight Accessors and Fallback Defaults
     * Branch: If weight attribute <= 0, fallback to default (Structural: 40, Electrical: 25, Piping: 20, Finishing: 15)
     */
    public function test_project_weight_accessors_and_fallbacks()
    {
        $projectDefault = new Project();
        $this->assertEquals(40, $projectDefault->structural_weight);
        $this->assertEquals(25, $projectDefault->electrical_weight);
        $this->assertEquals(20, $projectDefault->piping_weight);
        $this->assertEquals(15, $projectDefault->finishing_weight);

        $projectCustom = new Project([
            'structural_weight' => 50,
            'electrical_weight' => 20,
            'piping_weight' => 15,
            'finishing_weight' => 15,
        ]);
        $this->assertEquals(50, $projectCustom->structural_weight);
        $this->assertEquals(20, $projectCustom->electrical_weight);
        $this->assertEquals(15, $projectCustom->piping_weight);
        $this->assertEquals(15, $projectCustom->finishing_weight);
    }

    /**
     * White-Box Test: Trade Progress Recalculation & Weighted Overall Progress
     * Tests:
     * - recalculateTradeProgressFromTasks() computes average per category
     * - getCalculatedOverallProgressAttribute computes weighted score
     * - Auto-transitions project status to completed when all tasks reach 100
     * - Auto-reverts status to in_progress when overall progress falls below 100
     */
    public function test_recalculate_trade_progress_and_status_auto_transitions()
    {
        $project = Project::create([
            'project_code' => 'PRJ-PROG-01',
            'title' => 'Progress Test Project',
            'client_name' => 'Test Client',
            'location' => 'San Juan',
            'land_area_sqm' => 300.0,
            'floor_area_sqm' => 400.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
            'structural_weight' => 40,
            'electrical_weight' => 25,
            'piping_weight' => 20,
            'finishing_weight' => 15,
        ]);

        // Create tasks for each trade with dates
        ProjectTask::create(['project_id' => $project->id, 'task_name' => 'S1', 'category' => 'Structural', 'start_date' => '2026-01-01', 'due_date' => '2026-02-01', 'progress' => 80, 'status' => 'in_progress']);
        ProjectTask::create(['project_id' => $project->id, 'task_name' => 'S2', 'category' => 'Structural', 'start_date' => '2026-01-01', 'due_date' => '2026-02-01', 'progress' => 100, 'status' => 'completed']);
        // Structural avg: (80 + 100) / 2 = 90%

        ProjectTask::create(['project_id' => $project->id, 'task_name' => 'E1', 'category' => 'Electrical', 'start_date' => '2026-01-01', 'due_date' => '2026-02-01', 'progress' => 60, 'status' => 'in_progress']);
        // Electrical avg: 60%

        ProjectTask::create(['project_id' => $project->id, 'task_name' => 'P1', 'category' => 'Piping & Plumbing', 'start_date' => '2026-01-01', 'due_date' => '2026-02-01', 'progress' => 40, 'status' => 'in_progress']);
        // Piping avg: 40%

        ProjectTask::create(['project_id' => $project->id, 'task_name' => 'F1', 'category' => 'Finishing', 'start_date' => '2026-01-01', 'due_date' => '2026-02-01', 'progress' => 20, 'status' => 'in_progress']);
        // Finishing avg: 20%

        $project->recalculateTradeProgressFromTasks();
        $project->refresh();

        $this->assertEquals(90, $project->structural_progress);
        $this->assertEquals(60, $project->electrical_progress);
        $this->assertEquals(40, $project->piping_progress);
        $this->assertEquals(20, $project->finishing_progress);

        // Weighted calculation:
        // (90*40 + 60*25 + 40*20 + 20*15) / (40 + 25 + 20 + 15)
        // = (3600 + 1500 + 800 + 300) / 100 = 6200 / 100 = 62%
        $this->assertEquals(62, $project->overall_progress);
        $this->assertEquals('in_progress', $project->status);

        // Now update all tasks to 100% completed
        ProjectTask::where('project_id', $project->id)->update(['progress' => 100, 'status' => 'completed']);
        $project->recalculateTradeProgressFromTasks();
        $project->refresh();

        $this->assertEquals(100, $project->overall_progress);
        $this->assertEquals('completed', $project->status);
        $this->assertNotNull($project->actual_completion_date);

        // Revert a task to 50%
        ProjectTask::where('project_id', $project->id)->first()->update(['progress' => 50, 'status' => 'in_progress']);
        $project->recalculateTradeProgressFromTasks();
        $project->refresh();

        $this->assertLessThan(100, $project->overall_progress);
        $this->assertEquals('in_progress', $project->status);
        $this->assertNull($project->actual_completion_date);
    }

    /**
     * White-Box Test: seedDefaultChecklist() Method
     * Tests seeding 4 categories with scaled quantities according to floor_area_sqm
     */
    public function test_seed_default_checklist_tasks_and_materials()
    {
        $project = Project::create([
            'project_code' => 'PRJ-SEED-01',
            'title' => 'Seed Test Residence',
            'client_name' => 'Seed Client',
            'location' => 'Antipolo',
            'land_area_sqm' => 150.0,
            'floor_area_sqm' => 200.0, // areaMultiplier = 200 / 100 = 2.0
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);

        $project->seedDefaultChecklist();

        $this->assertGreaterThan(20, $project->tasks()->count());
        $this->assertGreaterThan(0, $project->structuralTasks()->count());
        $this->assertGreaterThan(0, $project->electricalTasks()->count());
        $this->assertGreaterThan(0, $project->pipingTasks()->count());
        $this->assertGreaterThan(0, $project->finishingTasks()->count());

        // Test that re-seeding without force does not duplicate
        $countBefore = $project->tasks()->count();
        $project->seedDefaultChecklist(false);
        $this->assertEquals($countBefore, $project->tasks()->count());

        // Test force reseeding resets and regenerates
        $project->seedDefaultChecklist(true);
        $this->assertEquals($countBefore, $project->tasks()->count());
    }

    /**
     * White-Box Test: getActiveMaterialsData() Method
     * Tests aggregation of active task materials and direct site material transfers (Roofing/Windows portal tags)
     */
    public function test_get_active_materials_data()
    {
        $project = Project::create([
            'project_code' => 'PRJ-ACT-01',
            'title' => 'Active Materials Project',
            'client_name' => 'Client Act',
            'location' => 'Makati',
            'land_area_sqm' => 200.0,
            'floor_area_sqm' => 300.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-10-31',
            'status' => 'in_progress',
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'task_name' => 'Formwork Assembly',
            'category' => 'Structural',
            'start_date' => '2026-01-10',
            'due_date' => '2026-01-25',
            'status' => 'in_progress',
            'progress' => 40,
        ]);

        ProjectTaskMaterial::create([
            'project_task_id' => $task->id,
            'material_name' => 'Phenolic Plywood Board',
            'category' => 'Structural',
            'unit' => 'pcs',
            'unit_cost' => 1150.00,
            'quantity' => 10,
        ]);

        // Add site material with transfer ref
        $mat = Material::create([
            'material_code' => 'MAT-ROOF-ACT',
            'name' => 'Corrugated Roof Sheet',
            'category' => 'Roofing',
            'unit' => 'pcs',
            'unit_cost' => 550.00,
            'stock_quantity' => 50,
        ]);

        ProjectMaterial::create([
            'project_id' => $project->id,
            'material_id' => $mat->id,
            'allocated_qty' => 30,
            'used_qty' => 10,
            'excess_returned_qty' => 0,
            'unit_price' => 550.00,
        ]);

        ProjectMaterialTransfer::create([
            'source_project_id' => $project->id,
            'destination_project_id' => $project->id,
            'material_id' => $mat->id,
            'quantity_transferred' => 30,
            'transfer_date' => now(),
            'transfer_reference_no' => 'ROOF-XFER-2026-001',
        ]);

        $data = $project->getActiveMaterialsData();

        $this->assertIsArray($data['materials']);
        $this->assertGreaterThanOrEqual(2, count($data['materials']));
        $this->assertGreaterThan(0, $data['total_active_value']);
        $this->assertEquals(1, $data['active_tasks_count']);
        $this->assertEquals(1, $data['in_progress_tasks_count']);
    }

    /**
     * White-Box Test: Schedule & Timeline Calculations
     * - total_schedule_days
     * - elapsed_days
     * - remaining_days
     * - schedule_progress_ratio
     * - getScheduleHealthStatusAttribute branches:
     *   - completed
     *   - overdue (now > end_date)
     *   - ahead (variance >= 5)
     *   - on_track (variance >= -10)
     *   - critical_lag (variance < -10)
     */
    public function test_schedule_calculations_and_health_status_branches()
    {
        // 1. Completed status
        $completedProject = new Project([
            'status' => 'completed',
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
        ]);
        $badgeComp = $completedProject->schedule_health_status;
        $this->assertEquals('completed', $badgeComp['status']);
        $this->assertEquals(0, $completedProject->remaining_days);

        // 2. Overdue project
        $overdueProject = new Project([
            'status' => 'in_progress',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-01',
            'overall_progress' => 70,
        ]);
        $badgeOverdue = $overdueProject->schedule_health_status;
        $this->assertEquals('delayed', $badgeOverdue['status']);
        $this->assertStringContainsString('Overdue', $badgeOverdue['label']);

        // 3. Ahead of schedule (variance >= 5)
        $aheadProject = new Project([
            'status' => 'in_progress',
            'start_date' => now()->subDays(20),
            'end_date' => now()->addDays(80),
            'overall_progress' => 50, // elapsed is 20%, actual is 50% (+30% variance)
        ]);
        $badgeAhead = $aheadProject->schedule_health_status;
        $this->assertEquals('ahead', $badgeAhead['status']);

        // 4. Critical Lag (variance < -10)
        $lagProject = new Project([
            'status' => 'in_progress',
            'start_date' => now()->subDays(60),
            'end_date' => now()->addDays(40),
            'overall_progress' => 10, // elapsed is 60%, actual is 10% (-50% variance)
        ]);
        $badgeLag = $lagProject->schedule_health_status;
        $this->assertEquals('critical_lag', $badgeLag['status']);
    }

    /**
     * White-Box Test: Manpower Deployment and Breakdown Calculations
     */
    public function test_manpower_deployment_and_breakdown()
    {
        $project = new Project([
            'deployed_workers' => 20,
            'deployed_skilled_workers' => 10,
            'deployed_engineers' => 3,
            'deployed_architects' => 2,
            'deployed_foremen' => 2,
            'deployed_operators' => 1,
            'deployed_safety_officers' => 2,
        ]);

        // Total = 20 + 10 + 3 + 2 + 2 + 1 + 2 = 40
        $this->assertEquals(40, $project->total_deployed_manpower);

        $breakdown = $project->manpower_breakdown;
        $this->assertArrayHasKey('workers', $breakdown);
        $this->assertArrayHasKey('skilled_workers', $breakdown);
        $this->assertArrayHasKey('engineers', $breakdown);
        $this->assertArrayHasKey('architects', $breakdown);
        $this->assertArrayHasKey('operators', $breakdown);
        $this->assertArrayHasKey('foremen', $breakdown);
        $this->assertArrayHasKey('safety_officers', $breakdown);

        // Workers % = 20 / 40 * 100 = 50%
        $this->assertEquals(50.0, $breakdown['workers']['percent']);
    }

    /**
     * White-Box Test: Financial, Budget, Gross Margin, and Cost Metrics
     */
    public function test_financial_and_costing_metrics()
    {
        $project = Project::create([
            'project_code' => 'PRJ-FIN-01',
            'title' => 'Financial Metrics Project',
            'client_name' => 'Client Fin',
            'location' => 'Ortigas',
            'land_area_sqm' => 200.0,
            'floor_area_sqm' => 300.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'contract_budget' => 1000000.00,
            'spent_budget' => 450000.00,
            'status' => 'in_progress',
        ]);

        $this->assertEquals(550000.00, $project->remaining_budget);
        $this->assertEquals(45.0, $project->budget_usage_percent);

        // Add ProjectCost records with cost_date
        ProjectCost::create([
            'project_id' => $project->id,
            'cost_code' => 'C-01',
            'cost_category' => 'Materials & Consumables',
            'item_name' => 'Steel and Cement',
            'estimated_cost' => 500000.00,
            'actual_cost' => 420000.00,
            'cost_date' => '2026-02-01',
        ]);

        ProjectCost::create([
            'project_id' => $project->id,
            'cost_code' => 'C-02',
            'cost_category' => 'Labor & Engineering',
            'item_name' => 'Direct Labor',
            'estimated_cost' => 300000.00,
            'actual_cost' => 280000.00,
            'cost_date' => '2026-02-15',
        ]);

        $project->refresh();

        // Incurred = 420000 + 280000 = 700000
        $this->assertEquals(700000.00, $project->total_incurred_cost);
        // Estimated = 500000 + 300000 = 800000
        $this->assertEquals(800000.00, $project->total_estimated_cost);
        // Gross margin = 1000000 - 700000 = 300000
        $this->assertEquals(300000.00, $project->gross_margin);
        // Gross margin % = 300000 / 1000000 * 100 = 30.0%
        $this->assertEquals(30.0, $project->gross_margin_percent);
        // Cost variance = 800000 - 700000 = 100000
        $this->assertEquals(100000.00, $project->cost_variance);
        // Cost per floor sqm = 700000 / 300 = 2333.33
        $this->assertEquals(2333.33, $project->cost_per_floor_sqm);
        // Cost per land sqm = 700000 / 200 = 3500.00
        $this->assertEquals(3500.00, $project->cost_per_land_sqm);
        // Health status (700000 / 1000000 = 0.70 < 0.85 -> healthy)
        $this->assertEquals('healthy', $project->cost_health_status);
    }

    /**
     * White-Box Test: Loan Financing & Disbursement Summary
     */
    public function test_loan_financing_and_summary_calculations()
    {
        $project = Project::create([
            'project_code' => 'PRJ-LOAN-01',
            'title' => 'Loan Financed Villa',
            'client_name' => 'Client Loan',
            'location' => 'Alabang',
            'land_area_sqm' => 400.0,
            'floor_area_sqm' => 500.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'contract_budget' => 2000000.00,
            'approved_loan_amount' => 1600000.00,
            'client_equity_amount' => 400000.00,
            'financing_type' => 'bank_loan',
            'status' => 'in_progress',
        ]);

        Payment::create([
            'project_id' => $project->id,
            'invoice_no' => 'INV-EQ-001',
            'payment_stage' => 'Equity Tranche 1',
            'official_receipt_no' => 'OR-EQ-001',
            'amount' => 400000.00,
            'payment_date' => now(),
            'financing_type' => 'client_equity',
            'status' => 'paid',
        ]);

        Payment::create([
            'project_id' => $project->id,
            'invoice_no' => 'INV-LN-001',
            'payment_stage' => 'Bank Drawdown 1',
            'official_receipt_no' => 'OR-LN-001',
            'amount' => 600000.00,
            'payment_date' => now(),
            'financing_type' => 'bank_loan',
            'status' => 'paid',
        ]);

        $project->refresh();

        $this->assertEquals(600000.00, $project->total_loan_disbursed);
        $this->assertEquals(400000.00, $project->total_equity_paid);
        $this->assertEquals(1000000.00, $project->pending_loan_disbursement); // 1600000 - 600000 = 1000000

        $summary = $project->financing_summary;
        $this->assertEquals(1000000.00, $summary['total_paid']);
        $this->assertEquals(1000000.00, $summary['remaining_receivable']);
        $this->assertEquals(50.0, $summary['percent_collected']);
        $this->assertTrue($summary['payment_first_cleared']);
    }

    /**
     * White-Box Test: Scope Cost Aggregation Attributes
     */
    public function test_scope_cost_aggregation_attributes()
    {
        $project = Project::create([
            'project_code' => 'PRJ-SCOPE-SUM',
            'title' => 'Scope Rollup Project',
            'client_name' => 'Client Rollup',
            'location' => 'Mandaluyong',
            'land_area_sqm' => 300.0,
            'floor_area_sqm' => 350.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);

        ProjectScopeItem::create([
            'project_id' => $project->id,
            'item_number' => '1.0',
            'item_name' => 'Item 1',
            'materials_subtotal' => 50000.00,
            'labor_subtotal' => 20000.00,
            'equipment_subtotal' => 10000.00,
            'direct_cost' => 80000.00,
            'total_item_cost' => 100000.00,
        ]);

        ProjectScopeItem::create([
            'project_id' => $project->id,
            'item_number' => '2.0',
            'item_name' => 'Item 2',
            'materials_subtotal' => 30000.00,
            'labor_subtotal' => 15000.00,
            'equipment_subtotal' => 5000.00,
            'direct_cost' => 50000.00,
            'total_item_cost' => 60000.00,
        ]);

        $project->refresh();

        $this->assertEquals(160000.00, $project->grand_scope_cost);
        $this->assertEquals(80000.00, $project->total_scope_materials_cost);
        $this->assertEquals(35000.00, $project->total_scope_labor_cost);
        $this->assertEquals(15000.00, $project->total_scope_equipment_cost);
        $this->assertEquals(130000.00, $project->total_scope_direct_cost);
    }
}
