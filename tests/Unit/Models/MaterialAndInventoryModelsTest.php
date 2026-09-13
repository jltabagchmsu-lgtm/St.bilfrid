<?php

namespace Tests\Unit\Models;

use App\Models\DailyMaterialUsage;
use App\Models\InventoryLog;
use App\Models\Material;
use App\Models\Project;
use App\Models\ProjectMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialAndInventoryModelsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: Material Model Casts and Relationships
     */
    public function test_material_model_casts_and_relationships()
    {
        $material = Material::create([
            'material_code' => 'MAT-TEST-001',
            'name' => '12mm Deformed Bar Grade 40',
            'category' => 'Structural & Masonry',
            'unit' => 'pcs',
            'unit_cost' => 310.50,
            'stock_quantity' => 200,
            'is_new_product' => true,
            'last_purchased_at' => now(),
        ]);

        $this->assertIsFloat($material->unit_cost);
        $this->assertEquals(310.50, $material->unit_cost);
        $this->assertIsInt($material->stock_quantity);
        $this->assertEquals(200, $material->stock_quantity);
        $this->assertTrue($material->is_new_product);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $material->last_purchased_at);

        $project = Project::create([
            'project_code' => 'PRJ-MAT-01',
            'title' => 'Horizon Villa',
            'client_name' => 'John Doe',
            'location' => 'Taguig City',
            'land_area_sqm' => 200.0,
            'floor_area_sqm' => 280.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-10-31',
            'status' => 'in_progress',
        ]);

        $projMat = ProjectMaterial::create([
            'project_id' => $project->id,
            'material_id' => $material->id,
            'allocated_qty' => 50,
            'used_qty' => 20,
            'excess_returned_qty' => 5,
            'unit_price' => 310.50,
        ]);

        $log = InventoryLog::create([
            'material_id' => $material->id,
            'project_id' => $project->id,
            'transaction_type' => 'restock',
            'quantity' => 200,
            'unit_cost' => 310.50,
            'reference_no' => 'RESTOCK-001',
        ]);

        $this->assertCount(1, $material->projectMaterials);
        $this->assertEquals($projMat->id, $material->projectMaterials->first()->id);

        $this->assertCount(1, $material->inventoryLogs);
        $this->assertEquals($log->id, $material->inventoryLogs->first()->id);
    }

    /**
     * White-Box Test: InventoryLog getTransactionBadgeAttribute Branch Coverage
     * Path 1: 'excess_return' -> 'Excess Material Returned' (#10b981)
     * Path 2: 'allocation' -> 'Site BOM Allocation' (#38bdf8)
     * Path 3: 'usage' -> 'Site Consumption Recorded' (#f59e0b)
     * Path 4: 'restock' -> 'Warehouse Restock / PO' (#8b5cf6)
     * Path 5: default ('adjustment') -> 'Inventory Adjustment' (#94a3b8)
     */
    public function test_inventory_log_transaction_badge_branches()
    {
        $types = [
            'excess_return' => ['label' => 'Excess Material Returned', 'color' => '#10b981'],
            'allocation' => ['label' => 'Site BOM Allocation', 'color' => '#38bdf8'],
            'usage' => ['label' => 'Site Consumption Recorded', 'color' => '#f59e0b'],
            'restock' => ['label' => 'Warehouse Restock / PO', 'color' => '#8b5cf6'],
            'manual_correction' => ['label' => 'Inventory Adjustment', 'color' => '#94a3b8'],
        ];

        foreach ($types as $type => $expected) {
            $log = new InventoryLog(['transaction_type' => $type]);
            $badge = $log->transaction_badge;
            $this->assertEquals($expected['label'], $badge['label']);
            $this->assertEquals($expected['color'], $badge['color']);
            $this->assertArrayHasKey('bg', $badge);
            $this->assertArrayHasKey('border', $badge);
        }
    }

    /**
     * White-Box Test: InventoryLog Relationships & Casts
     */
    public function test_inventory_log_relationships_and_casts()
    {
        $project = Project::create([
            'project_code' => 'PRJ-LOG-01',
            'title' => 'Project Log Test',
            'client_name' => 'Client A',
            'location' => 'Makati',
            'land_area_sqm' => 180.0,
            'floor_area_sqm' => 220.0,
            'start_date' => '2026-02-01',
            'end_date' => '2026-11-30',
            'status' => 'in_progress',
        ]);

        $material = Material::create([
            'material_code' => 'MAT-LOG-01',
            'name' => 'CHB 4 Inch',
            'category' => 'Structural & Masonry',
            'unit' => 'pcs',
            'unit_cost' => 14.50,
            'stock_quantity' => 1000,
        ]);

        $log = InventoryLog::create([
            'material_id' => $material->id,
            'project_id' => $project->id,
            'transaction_type' => 'allocation',
            'quantity' => 500,
            'unit_cost' => 14.50,
            'reference_no' => 'ALLOC-001',
            'notes' => 'Allocated to ground floor',
        ]);

        $this->assertIsInt($log->quantity);
        $this->assertEquals(500, $log->quantity);
        $this->assertIsFloat($log->unit_cost);
        $this->assertEquals(14.50, $log->unit_cost);

        $this->assertInstanceOf(Material::class, $log->material);
        $this->assertInstanceOf(Project::class, $log->project);
    }

    /**
     * White-Box Test: DailyMaterialUsage Model Casts & Relationships
     */
    public function test_daily_material_usage_casts_and_relationships()
    {
        $project = Project::create([
            'project_code' => 'PRJ-DMU-01',
            'title' => 'DMU Test Project',
            'client_name' => 'Client DMU',
            'location' => 'Pasig City',
            'land_area_sqm' => 300.0,
            'floor_area_sqm' => 400.0,
            'start_date' => '2026-01-15',
            'end_date' => '2026-12-15',
            'status' => 'in_progress',
        ]);

        $material = Material::create([
            'material_code' => 'MAT-DMU-01',
            'name' => 'Portland Cement Bag',
            'category' => 'Structural & Masonry',
            'unit' => 'bags',
            'unit_cost' => 230.00,
            'stock_quantity' => 100,
        ]);

        $projMat = ProjectMaterial::create([
            'project_id' => $project->id,
            'material_id' => $material->id,
            'allocated_qty' => 50,
            'used_qty' => 15,
            'excess_returned_qty' => 0,
            'unit_price' => 230.00,
        ]);

        $usage = DailyMaterialUsage::create([
            'project_id' => $project->id,
            'project_material_id' => $projMat->id,
            'material_id' => $material->id,
            'usage_date' => '2026-09-10',
            'quantity_used' => 15.5,
            'activity_description' => 'Foundation footing pour',
            'logged_by' => 'Engr. Santos',
            'notes' => 'Completed on schedule',
        ]);

        $this->assertIsFloat($usage->quantity_used);
        $this->assertEquals(15.5, $usage->quantity_used);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $usage->usage_date);

        $this->assertInstanceOf(Project::class, $usage->project);
        $this->assertInstanceOf(ProjectMaterial::class, $usage->projectMaterial);
        $this->assertInstanceOf(Material::class, $usage->material);
    }
}
