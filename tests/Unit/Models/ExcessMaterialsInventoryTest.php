<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Material;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\InventoryLog;
use App\Models\ProjectMaterialTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcessMaterialsInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create authenticated admin user
        $user = User::create([
            'name' => 'Lead Engineer',
            'email' => 'engineer@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($user);
    }

    /**
     * Test batch excess materials return from completed project to Central Warehouse Inventory
     */
    public function test_batch_return_excess_materials_to_inventory()
    {
        $project = Project::create([
            'project_code' => 'PRJ-2026-COMP-01',
            'title' => 'Residence Turnover Villa',
            'client_name' => 'Engr. Smith',
            'location' => 'Block 5 Lot 12',
            'project_type' => 'Residential Build',
            'status' => 'completed',
            'land_area_sqm' => 250,
            'floor_area_sqm' => 180,
            'contract_budget' => 4500000.00,
            'spent_budget' => 3800000.00,
            'start_date' => now()->subMonths(6),
            'end_date' => now(),
            'actual_completion_date' => now(),
            'structural_progress' => 100,
            'electrical_progress' => 100,
            'piping_progress' => 100,
            'finishing_progress' => 100,
            'overall_progress' => 100,
        ]);

        $cement = Material::create([
            'material_code' => 'MAT-CEM-01',
            'name' => 'Portland Cement Type 1',
            'category' => 'Structural & Masonry',
            'unit' => 'bags',
            'unit_cost' => 280.00,
            'stock_quantity' => 50,
        ]);

        $rebar = Material::create([
            'material_code' => 'MAT-REB-16',
            'name' => 'Deformed Steel Bar 16mm',
            'category' => 'Structural & Masonry',
            'unit' => 'pcs',
            'unit_cost' => 420.00,
            'stock_quantity' => 100,
        ]);

        $pmCement = ProjectMaterial::create([
            'project_id' => $project->id,
            'material_id' => $cement->id,
            'allocated_qty' => 200,
            'used_qty' => 170,
            'excess_returned_qty' => 0,
            'unit_price' => 280.00,
        ]);

        $pmRebar = ProjectMaterial::create([
            'project_id' => $project->id,
            'material_id' => $rebar->id,
            'allocated_qty' => 150,
            'used_qty' => 120,
            'excess_returned_qty' => 0,
            'unit_price' => 420.00,
        ]);

        $this->assertEquals(30, $pmCement->remaining_qty); // 200 - 170 = 30
        $this->assertEquals(30, $pmRebar->remaining_qty); // 150 - 120 = 30

        // Submit batch return request
        $response = $this->post(route('projects.returnExcessBatch', $project->id), [
            'transfer_date' => now()->toDateString(),
            'general_notes' => 'Surplus reclaimed upon client handover.',
            'materials' => [
                [
                    'selected' => '1',
                    'project_material_id' => $pmCement->id,
                    'return_qty' => 25,
                    'notes' => '25 bags unused Portland cement returned to warehouse rack A',
                ],
                [
                    'selected' => '1',
                    'project_material_id' => $pmRebar->id,
                    'return_qty' => 30,
                    'notes' => '30 pcs 16mm rebar bars returned to steel yard',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify warehouse stock was incremented
        $cement->refresh();
        $rebar->refresh();
        $this->assertEquals(75, $cement->stock_quantity); // 50 + 25 = 75
        $this->assertEquals(130, $rebar->stock_quantity); // 100 + 30 = 130

        // Verify project material balances
        $pmCement->refresh();
        $pmRebar->refresh();
        $this->assertEquals(25, $pmCement->excess_returned_qty);
        $this->assertEquals(5, $pmCement->remaining_qty); // 30 - 25 = 5
        $this->assertEquals(30, $pmRebar->excess_returned_qty);
        $this->assertEquals(0, $pmRebar->remaining_qty); // 30 - 30 = 0

        // Verify inventory audit logs created
        $logs = InventoryLog::where('project_id', $project->id)->where('transaction_type', 'excess_return')->get();
        $this->assertCount(2, $logs);

        $cementLog = $logs->firstWhere('material_id', $cement->id);
        $this->assertNotNull($cementLog);
        $this->assertEquals(25, $cementLog->quantity);
        $this->assertEquals(280.00, $cementLog->unit_cost);
        $this->assertStringContainsString('Portland Cement Type 1', $cementLog->notes);

        // Verify transfer audit vouchers
        $transfers = ProjectMaterialTransfer::where('source_project_id', $project->id)->get();
        $this->assertCount(2, $transfers);
    }

    /**
     * Test 1-Click return all remaining excess materials from project
     */
    public function test_1_click_return_all_excess_materials()
    {
        $project = Project::create([
            'project_code' => 'PRJ-2026-COMP-02',
            'title' => 'Commercial Pavilion Build',
            'client_name' => 'Metro Retail Corp',
            'location' => 'CBD Sector 4',
            'project_type' => 'Commercial Construction',
            'status' => 'completed',
            'land_area_sqm' => 400,
            'floor_area_sqm' => 320,
            'contract_budget' => 8000000.00,
            'spent_budget' => 7100000.00,
            'start_date' => now()->subMonths(4),
            'end_date' => now(),
            'actual_completion_date' => now(),
            'overall_progress' => 100,
        ]);

        $tiles = Material::create([
            'material_code' => 'MAT-TIL-60',
            'name' => 'Ceramic Floor Tiles 60x60cm',
            'category' => 'Architectural & Finishes',
            'unit' => 'boxes',
            'unit_cost' => 650.00,
            'stock_quantity' => 20,
        ]);

        $pmTiles = ProjectMaterial::create([
            'project_id' => $project->id,
            'material_id' => $tiles->id,
            'allocated_qty' => 100,
            'used_qty' => 85,
            'excess_returned_qty' => 0,
            'unit_price' => 650.00,
        ]);

        $response = $this->post(route('projects.returnExcessBatch', $project->id), [
            'return_all' => 1,
            'transfer_date' => now()->toDateString(),
            'general_notes' => '1-Click full site excess reconciliation upon turnover.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $tiles->refresh();
        $pmTiles->refresh();

        $this->assertEquals(35, $tiles->stock_quantity); // 20 + 15 = 35
        $this->assertEquals(15, $pmTiles->excess_returned_qty);
        $this->assertEquals(0, $pmTiles->remaining_qty);
    }

    /**
     * Test adding extra / unlisted surplus materials discovered on site after project completion
     */
    public function test_add_custom_excess_material_discovered_on_site()
    {
        $project = Project::create([
            'project_code' => 'PRJ-2026-COMP-03',
            'title' => 'Residential Townhouse Complex',
            'client_name' => 'Mrs. Garcia',
            'location' => 'Emerald Heights',
            'project_type' => 'Residential Build',
            'status' => 'completed',
            'land_area_sqm' => 300,
            'floor_area_sqm' => 240,
            'contract_budget' => 6000000.00,
            'spent_budget' => 5200000.00,
            'start_date' => now()->subMonths(5),
            'end_date' => now(),
            'actual_completion_date' => now(),
            'overall_progress' => 100,
        ]);

        $response = $this->post(route('projects.addCustomExcess', $project->id), [
            'custom_material_name' => 'Surplus PVC Conduit Pipe 20mm',
            'category' => 'Electrical Works',
            'unit' => 'pcs',
            'quantity' => 45,
            'unit_cost' => 110.00,
            'transfer_date' => now()->toDateString(),
            'notes' => 'Unused bundles of electrical conduits recovered from finishing storage.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify material was created or updated with stock
        $mat = Material::where('name', 'Surplus PVC Conduit Pipe 20mm')->first();
        $this->assertNotNull($mat);
        $this->assertEquals(45, $mat->stock_quantity);
        $this->assertEquals('Electrical Works', $mat->category);

        // Verify ProjectMaterial was created and credited
        $pm = ProjectMaterial::where('project_id', $project->id)->where('material_id', $mat->id)->first();
        $this->assertNotNull($pm);
        $this->assertEquals(45, $pm->excess_returned_qty);

        // Verify InventoryLog
        $log = InventoryLog::where('project_id', $project->id)->where('material_id', $mat->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('excess_return', $log->transaction_type);
        $this->assertEquals(45, $log->quantity);
    }

    /**
     * Test JSON endpoint for modal dynamic material fetching
     */
    public function test_get_excess_materials_json_endpoint()
    {
        $project = Project::create([
            'project_code' => 'PRJ-2026-COMP-04',
            'title' => 'Duplex Master Suite',
            'client_name' => 'Dr. Chen',
            'location' => 'Highland Hills',
            'project_type' => 'Residential Build',
            'status' => 'completed',
            'land_area_sqm' => 280,
            'floor_area_sqm' => 200,
            'contract_budget' => 5000000.00,
            'spent_budget' => 4200000.00,
            'start_date' => now()->subMonths(6),
            'end_date' => now(),
            'actual_completion_date' => now(),
            'overall_progress' => 100,
        ]);

        $paint = Material::create([
            'material_code' => 'MAT-PNT-01',
            'name' => 'Premium Acrylic Semi-Gloss White',
            'category' => 'Architectural & Finishes',
            'unit' => 'pails',
            'unit_cost' => 2400.00,
            'stock_quantity' => 10,
        ]);

        ProjectMaterial::create([
            'project_id' => $project->id,
            'material_id' => $paint->id,
            'allocated_qty' => 20,
            'used_qty' => 16,
            'excess_returned_qty' => 0,
            'unit_price' => 2400.00,
        ]);

        $response = $this->get(route('projects.excessMaterialsJson', $project->id));
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'project_id' => $project->id,
            'project_code' => 'PRJ-2026-COMP-04',
            'total_excess_units' => 4,
        ]);
    }
}
