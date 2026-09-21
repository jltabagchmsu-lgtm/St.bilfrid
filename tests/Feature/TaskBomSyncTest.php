<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskMaterial;
use App\Models\ProjectMaterial;
use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskBomSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::create([
            'name' => 'Admin Test User',
            'email' => 'admin.test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    /**
     * Test adding a task with materials automatically populates ProjectTaskMaterial and BOM Master Table
     */
    public function test_add_task_with_materials_syncs_to_bom_master_table()
    {
        $project = Project::create([
            'project_code' => 'PRJ-SYNC-01',
            'title' => 'BOM Sync Test Project',
            'client_name' => 'John Doe',
            'status' => 'in_progress',
            'land_area_sqm' => 250,
            'floor_area_sqm' => 180,
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'contract_budget' => 5000000,
        ]);

        // Add task with 2 materials
        $response = $this->actingAs($this->adminUser)->post(route('projects.addTask', $project->id), [
            'task_name' => 'Footing and Column Concrete Pouring',
            'category' => 'Structural',
            'progress' => 30,
            'status' => 'in_progress',
            'materials' => [
                [
                    'name' => 'Portland Cement Type 1P',
                    'quantity' => 120,
                    'unit' => 'bags',
                    'unit_cost' => 240,
                ],
                [
                    'name' => '16mm Deformed Steel Bar Grade 60',
                    'quantity' => 80,
                    'unit' => 'pcs',
                    'unit_cost' => 460,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check task was created
        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $project->id,
            'task_name' => 'Footing and Column Concrete Pouring',
            'category' => 'Structural',
        ]);

        $task = ProjectTask::where('project_id', $project->id)->where('task_name', 'Footing and Column Concrete Pouring')->first();
        $this->assertNotNull($task);

        // Check task materials were created
        $this->assertEquals(2, $task->taskMaterials()->count());
        $this->assertDatabaseHas('project_task_materials', [
            'project_task_id' => $task->id,
            'material_name' => 'Portland Cement Type 1P',
            'quantity' => 120,
            'unit_cost' => 240,
            'total_cost' => 28800,
        ]);
        $this->assertDatabaseHas('project_task_materials', [
            'project_task_id' => $task->id,
            'material_name' => '16mm Deformed Steel Bar Grade 60',
            'quantity' => 80,
            'unit_cost' => 460,
            'total_cost' => 36800,
        ]);

        // Visit BOM page and ensure materials appear in response view
        $bomResponse = $this->actingAs($this->adminUser)->get(route('bom.index', ['project_id' => $project->id]));
        $bomResponse->assertStatus(200);
        $bomResponse->assertSee('Portland Cement Type 1P');
        $bomResponse->assertSee('16mm Deformed Steel Bar Grade 60');
        $bomResponse->assertSee('Footing and Column Concrete Pouring');
    }

    /**
     * Test Auto-Allocate from Task materials into Site Tracker
     */
    public function test_auto_allocate_from_task_materials_into_site_tracker()
    {
        $project = Project::create([
            'project_code' => 'PRJ-SYNC-02',
            'title' => 'Auto Allocate Test Project',
            'client_name' => 'Jane Smith',
            'status' => 'in_progress',
            'land_area_sqm' => 300,
            'floor_area_sqm' => 220,
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'contract_budget' => 3000000,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'task_name' => 'Electrical Conduit Roughing',
            'category' => 'Electrical',
            'start_date' => now(),
            'due_date' => now()->addMonths(2),
            'progress' => 0,
            'status' => 'not_started',
        ]);

        ProjectTaskMaterial::create([
            'project_task_id' => $task->id,
            'material_name' => '20mm PVC Electrical Conduit Pipe',
            'category' => 'Electrical',
            'unit' => 'lengths',
            'unit_cost' => 110,
            'quantity' => 50,
            'total_cost' => 5500,
            'status' => 'allocated',
        ]);

        // Trigger 1-click Auto Allocate
        $allocResponse = $this->actingAs($this->adminUser)->post(route('bom.autoAllocateScope', $project->id));
        $allocResponse->assertRedirect(route('bom.index', ['project_id' => $project->id]));
        $allocResponse->assertSessionHas('success');

        // Verify project material allocation created
        $this->assertDatabaseHas('materials', [
            'name' => '20mm PVC Electrical Conduit Pipe',
        ]);
        $this->assertDatabaseHas('project_materials', [
            'project_id' => $project->id,
            'allocated_qty' => 50,
        ]);
    }

    /**
     * Test Searching Purchase Orders by Supplier Name
     */
    public function test_search_orders_by_supplier_name()
    {
        $supplier = \App\Models\Supplier::create([
            'name' => 'Colorsteel Roofing Solutions Corp.',
            'code' => 'SUP-COLOR',
            'category' => 'Roofing & Metal Sheets',
            'email' => 'sales@colorsteel.test',
            'status' => 'active',
        ]);

        $otherSupplier = \App\Models\Supplier::create([
            'name' => 'Titan Structural Steel Corp.',
            'code' => 'SUP-TITAN',
            'category' => 'Structural Steel',
            'email' => 'orders@titansteel.test',
            'status' => 'active',
        ]);

        $order1 = \App\Models\SupplierOrder::create([
            'order_code' => 'PO-2026-001',
            'supplier_id' => $supplier->id,
            'ordered_by_user_id' => $this->adminUser->id,
            'delivery_location' => 'Main Warehouse Depot',
            'requested_delivery_date' => now()->addDays(5),
            'total_amount' => 85000,
            'status' => 'confirmed',
        ]);

        $order2 = \App\Models\SupplierOrder::create([
            'order_code' => 'PO-2026-002',
            'supplier_id' => $otherSupplier->id,
            'ordered_by_user_id' => $this->adminUser->id,
            'delivery_location' => 'Site San Juan',
            'requested_delivery_date' => now()->addDays(7),
            'total_amount' => 150000,
            'status' => 'confirmed',
        ]);

        // Search "Colorsteel"
        $response = $this->actingAs($this->adminUser)->get(route('admin.suppliers.orders', ['search' => 'Colorsteel']));
        $response->assertStatus(200);
        $response->assertSee('PO-2026-001');
        $response->assertSee('Colorsteel Roofing Solutions Corp.');
        $response->assertDontSee('PO-2026-002');
    }
}
