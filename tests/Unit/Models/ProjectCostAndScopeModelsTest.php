<?php

namespace Tests\Unit\Models;

use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectScopeItem;
use App\Models\ProjectScopeLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCostAndScopeModelsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: ProjectCost Variance and Variance Percent Branch Coverage
     * Path 1: estimated_cost <= 0 -> variance_percent is 0
     * Path 2: estimated_cost > 0 with actual_cost -> exact variance and percent calculation
     */
    public function test_project_cost_variance_calculations()
    {
        $costUnderBudget = new ProjectCost([
            'estimated_cost' => 100000.00,
            'actual_cost' => 85000.00,
        ]);
        $this->assertEquals(15000.00, $costUnderBudget->variance);
        $this->assertEquals(15.0, $costUnderBudget->variance_percent);

        $costOverrun = new ProjectCost([
            'estimated_cost' => 50000.00,
            'actual_cost' => 60000.00,
        ]);
        $this->assertEquals(-10000.00, $costOverrun->variance);
        $this->assertEquals(-20.0, $costOverrun->variance_percent);

        $costZeroEstimate = new ProjectCost([
            'estimated_cost' => 0.00,
            'actual_cost' => 5000.00,
        ]);
        $this->assertEquals(-5000.00, $costZeroEstimate->variance);
        $this->assertEquals(0.0, $costZeroEstimate->variance_percent);
    }

    /**
     * White-Box Test: ProjectScopeItem recalculate() Method and Branch Coverage
     * Path 1: Recalculate using markup percentages (contingency_percent, taxes_percent, profit_percent)
     * Path 2: Recalculate using explicit markup amounts override (contingency_amount > 0, etc.)
     */
    public function test_project_scope_item_recalculate_method_branches()
    {
        $project = Project::create([
            'project_code' => 'PRJ-SCOPE-01',
            'title' => 'Scope Calculation Project',
            'client_name' => 'Client Scope',
            'location' => 'BGC Taguig',
            'land_area_sqm' => 400.0,
            'floor_area_sqm' => 600.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);

        $scopeItem = ProjectScopeItem::create([
            'project_id' => $project->id,
            'item_number' => '1.0',
            'item_name' => 'Earthworks & Substructure',
            'contingency_percent' => 5.0,
            'taxes_percent' => 12.0,
            'profit_percent' => 10.0,
        ]);

        // Add scope lines for Material, Labor, and Equipment
        ProjectScopeLine::create([
            'project_scope_item_id' => $scopeItem->id,
            'category' => 'material',
            'description' => 'Sub-base Gravel',
            'quantity' => 10,
            'unit' => 'cu.m',
            'unit_price' => 1400.00,
            'total_cost' => 14000.00,
        ]);

        ProjectScopeLine::create([
            'project_scope_item_id' => $scopeItem->id,
            'category' => 'labor',
            'description' => 'Excavation Foreman & Crew',
            'quantity' => 1,
            'unit' => 'lot',
            'unit_price' => 6000.00,
            'total_cost' => 6000.00,
        ]);

        ProjectScopeLine::create([
            'project_scope_item_id' => $scopeItem->id,
            'category' => 'equipment',
            'description' => 'Mini-Excavator 3-Day Rental',
            'quantity' => 1,
            'unit' => 'lot',
            'unit_price' => 10000.00,
            'total_cost' => 10000.00,
        ]);

        // 1. Recalculate using percentages
        // Direct cost = 14000 + 6000 + 10000 = 30000
        // Contingency (5%) = 1500
        // Taxes (12%) = 3600
        // Profit (10%) = 3000
        // Total = 30000 + 1500 + 3600 + 3000 = 38100
        $scopeItem->recalculate();
        $scopeItem->refresh();

        $this->assertEquals(14000.00, $scopeItem->materials_subtotal);
        $this->assertEquals(6000.00, $scopeItem->labor_subtotal);
        $this->assertEquals(10000.00, $scopeItem->equipment_subtotal);
        $this->assertEquals(30000.00, $scopeItem->direct_cost);
        $this->assertEquals(1500.00, $scopeItem->contingency_amount);
        $this->assertEquals(3600.00, $scopeItem->taxes_amount);
        $this->assertEquals(3000.00, $scopeItem->profit_amount);
        $this->assertEquals(38100.00, $scopeItem->total_item_cost);

        // 2. Recalculate with explicit amount overrides
        $scopeItem->contingency_amount = 2000.00;
        $scopeItem->taxes_amount = 4000.00;
        $scopeItem->profit_amount = 5000.00;
        $scopeItem->save();

        $scopeItem->recalculate();
        $scopeItem->refresh();

        // Direct = 30000 + 2000 + 4000 + 5000 = 41000
        $this->assertEquals(41000.00, $scopeItem->total_item_cost);
    }

    /**
     * White-Box Test: ProjectScopeLine getRemainingQuantityAttribute Branch Coverage
     */
    public function test_project_scope_line_remaining_quantity_branches()
    {
        $lineNormal = new ProjectScopeLine([
            'quantity' => 100,
            'used_quantity' => 40,
            'excess_returned_quantity' => 10,
        ]);
        $this->assertEquals(50, $lineNormal->remaining_quantity);

        $lineExhausted = new ProjectScopeLine([
            'quantity' => 100,
            'used_quantity' => 90,
            'excess_returned_quantity' => 20,
        ]);
        $this->assertEquals(0, $lineExhausted->remaining_quantity); // clamped to 0
    }
}
