<?php

namespace Tests\Unit\Models;

use App\Models\Material;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\ProjectMaterialTransfer;
use App\Models\ProjectPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMaterialAndTransferModelsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: ProjectMaterial Calculation Accessors
     * - remaining_qty: max(0, allocated - used - excess)
     * - net_allocated_qty: max(0, allocated - excess)
     * - total_cost: allocated * unit_price
     * - net_cost: net_allocated * unit_price
     * - used_cost: used * unit_price
     * - returned_excess_value: excess * unit_price
     */
    public function test_project_material_calculation_accessors()
    {
        $pm = new ProjectMaterial([
            'allocated_qty' => 100,
            'used_qty' => 70,
            'excess_returned_qty' => 15,
            'unit_price' => 250.00,
        ]);

        $this->assertEquals(15, $pm->remaining_qty); // 100 - 70 - 15 = 15
        $this->assertEquals(85, $pm->net_allocated_qty); // 100 - 15 = 85
        $this->assertEquals(25000.00, $pm->total_cost); // 100 * 250
        $this->assertEquals(21250.00, $pm->net_cost); // 85 * 250
        $this->assertEquals(17500.00, $pm->used_cost); // 70 * 250
        $this->assertEquals(3750.00, $pm->returned_excess_value); // 15 * 250

        // Test boundary zero clamps
        $pmOverused = new ProjectMaterial([
            'allocated_qty' => 50,
            'used_qty' => 45,
            'excess_returned_qty' => 10,
            'unit_price' => 100.00,
        ]);
        $this->assertEquals(0, $pmOverused->remaining_qty); // clamped to 0
        $this->assertEquals(40, $pmOverused->net_allocated_qty); // 50 - 10
    }

    /**
     * White-Box Test: ProjectMaterialTransfer Model Casts & Relationships
     */
    public function test_project_material_transfer_model()
    {
        $projA = Project::create([
            'project_code' => 'PRJ-XFER-A',
            'title' => 'Site Alpha',
            'client_name' => 'Client A',
            'location' => 'Manila',
            'land_area_sqm' => 200.0,
            'floor_area_sqm' => 300.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-08-30',
            'status' => 'in_progress',
        ]);

        $projB = Project::create([
            'project_code' => 'PRJ-XFER-B',
            'title' => 'Site Beta',
            'client_name' => 'Client B',
            'location' => 'Quezon City',
            'land_area_sqm' => 250.0,
            'floor_area_sqm' => 350.0,
            'start_date' => '2026-02-01',
            'end_date' => '2026-09-30',
            'status' => 'in_progress',
        ]);

        $material = Material::create([
            'material_code' => 'MAT-XFER-01',
            'name' => 'Phenolic Board 1/2',
            'category' => 'Structural & Masonry',
            'unit' => 'pcs',
            'unit_cost' => 1150.00,
            'stock_quantity' => 100,
        ]);

        $transfer = ProjectMaterialTransfer::create([
            'source_project_id' => $projA->id,
            'destination_project_id' => $projB->id,
            'material_id' => $material->id,
            'quantity_transferred' => 25.0,
            'transfer_date' => '2026-09-11',
            'transfer_reference_no' => 'XFER-ROOF-001',
            'transfer_type' => 'inter_project',
            'reason' => 'Urgent site requirement',
            'authorized_by' => 'Engr. Davis',
        ]);

        $this->assertIsFloat($transfer->quantity_transferred);
        $this->assertEquals(25.0, $transfer->quantity_transferred);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $transfer->transfer_date);

        $this->assertInstanceOf(Project::class, $transfer->sourceProject);
        $this->assertEquals($projA->id, $transfer->sourceProject->id);

        $this->assertInstanceOf(Project::class, $transfer->destinationProject);
        $this->assertEquals($projB->id, $transfer->destinationProject->id);

        $this->assertInstanceOf(Material::class, $transfer->material);
    }

    /**
     * White-Box Test: ProjectPhoto getTypeBadgeAttribute Branch Coverage
     */
    public function test_project_photo_type_badge_branches()
    {
        $types = [
            'blueprint' => ['label' => 'Technical Blueprint / CAD', 'color' => '#38bdf8'],
            '3d_render' => ['label' => '3D Architectural Render (Target Design)', 'color' => '#ec4899'],
            'client_want' => ['label' => 'Client Design Inspiration', 'color' => '#a855f7'],
            'structural' => ['label' => 'Structural & Foundation Works', 'color' => '#f59e0b'],
            'finishing' => ['label' => 'Architectural & Turnkey Finishes', 'color' => '#10b981'],
            'site_actual' => ['label' => 'Actual On-Site Progress', 'color' => '#ef4444'],
        ];

        foreach ($types as $type => $expected) {
            $photo = new ProjectPhoto(['photo_type' => $type]);
            $badge = $photo->type_badge;
            $this->assertEquals($expected['label'], $badge['label']);
            $this->assertEquals($expected['color'], $badge['color']);
            $this->assertArrayHasKey('bg', $badge);
            $this->assertArrayHasKey('border', $badge);
        }
    }
}
