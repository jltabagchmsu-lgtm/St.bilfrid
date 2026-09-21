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

    /**
     * Test attaching a photo proof to a task marks it completed, updates photo path, and registers to project gallery
     */
    public function test_attach_photo_proof_to_task_updates_task_and_creates_project_photo()
    {
        $project = Project::create([
            'project_code' => 'PRJ-PROOF-01',
            'title' => 'Proof Photo Test Project',
            'client_name' => 'Michael Scott',
            'status' => 'in_progress',
            'land_area_sqm' => 400,
            'floor_area_sqm' => 280,
            'start_date' => now(),
            'end_date' => now()->addMonths(4),
            'contract_budget' => 4500000,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'task_name' => 'Foundation Rebar & Formworks Inspection',
            'category' => 'Structural',
            'progress' => 50,
            'status' => 'in_progress',
            'start_date' => now(),
            'due_date' => now()->addDays(10),
        ]);

        $fakePhoto = \Illuminate\Http\UploadedFile::fake()->create('rebar_proof.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($this->adminUser)->post(route('projects.tasks.attachPhoto', $task->id), [
            'photo_file' => $fakePhoto,
            'caption' => 'Rebar spacing inspected on site and verified by project lead.',
            'mark_completed' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $task->refresh();
        $this->assertNotNull($task->photo_path);
        $this->assertEquals('Rebar spacing inspected on site and verified by project lead.', $task->photo_caption);
        $this->assertEquals(100, $task->progress);
        $this->assertEquals('completed', $task->status);

        // Check project photo was added to gallery
        $this->assertDatabaseHas('project_photos', [
            'project_id' => $project->id,
            'file_path' => $task->photo_path,
        ]);
    }

    /**
     * Test removing an attached photo proof from a task
     */
    public function test_remove_photo_proof_from_task()
    {
        $project = Project::create([
            'project_code' => 'PRJ-PROOF-02',
            'title' => 'Remove Proof Test Project',
            'client_name' => 'Jim Halpert',
            'status' => 'in_progress',
            'land_area_sqm' => 350,
            'floor_area_sqm' => 200,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'contract_budget' => 3500000,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'task_name' => 'Main Distribution Panel Installation',
            'category' => 'Electrical',
            'start_date' => now(),
            'due_date' => now()->addMonths(1),
            'progress' => 100,
            'status' => 'completed',
            'photo_path' => '/uploads/tasks/proof_test_123.jpg',
            'photo_caption' => 'Panel energized and tested.',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('projects.tasks.removePhoto', $task->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $task->refresh();
        $this->assertNull($task->photo_path);
        $this->assertNull($task->photo_caption);
    }

    /**
     * Test client remaining balance calculation after milestone payments
     */
    public function test_client_remaining_balance_after_payment_calculation()
    {
        $project = Project::create([
            'project_code' => 'PRJ-BAL-01',
            'title' => 'Client Balance Test Project',
            'client_name' => 'Dwight Schrute',
            'status' => 'in_progress',
            'land_area_sqm' => 500,
            'floor_area_sqm' => 320,
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'contract_budget' => 6000000,
        ]);

        // Payment 1: ₱2,000,000 Downpayment
        $payment1 = \App\Models\Payment::create([
            'project_id' => $project->id,
            'official_receipt_no' => 'OR-2026-B01',
            'invoice_no' => 'INV-2026-B01',
            'payer_name' => 'Dwight Schrute',
            'amount' => 2000000,
            'payment_date' => now()->subDays(10),
            'payment_stage' => 'Downpayment (33.3%)',
            'payment_method' => 'Bank Transfer',
            'status' => 'paid',
        ]);

        $this->assertEquals(2000000, $payment1->cumulative_paid_up_to_this);
        $this->assertEquals(0, $payment1->prior_paid_before_this);
        $this->assertEquals(4000000, $payment1->remaining_balance_after_payment);

        // Payment 2: ₱2,500,000 Structural Milestone
        $payment2 = \App\Models\Payment::create([
            'project_id' => $project->id,
            'official_receipt_no' => 'OR-2026-B02',
            'invoice_no' => 'INV-2026-B02',
            'payer_name' => 'Dwight Schrute',
            'amount' => 2500000,
            'payment_date' => now()->subDays(2),
            'payment_stage' => 'Structural Frame Completion',
            'payment_method' => 'Bank Transfer',
            'status' => 'paid',
        ]);

        $this->assertEquals(4500000, $payment2->cumulative_paid_up_to_this);
        $this->assertEquals(2000000, $payment2->prior_paid_before_this);
        $this->assertEquals(1500000, $payment2->remaining_balance_after_payment);

        // Payment 3: ₱1,500,000 Final Handover Balance
        $payment3 = \App\Models\Payment::create([
            'project_id' => $project->id,
            'official_receipt_no' => 'OR-2026-B03',
            'invoice_no' => 'INV-2026-B03',
            'payer_name' => 'Dwight Schrute',
            'amount' => 1500000,
            'payment_date' => now(),
            'payment_stage' => 'Final Turnover Settlement',
            'payment_method' => 'Cheque',
            'status' => 'paid',
        ]);

        $this->assertEquals(6000000, $payment3->cumulative_paid_up_to_this);
        $this->assertEquals(4500000, $payment3->prior_paid_before_this);
        $this->assertEquals(0, $payment3->remaining_balance_after_payment);

        // Verify Printable OR View renders the remaining balance
        $orResponse = $this->actingAs($this->adminUser)->get(route('payments.printReceipt', $payment2->id));
        $orResponse->assertStatus(200);
        $orResponse->assertSee('Statement of Client Account & Remaining Balance Breakdown', false);
        $orResponse->assertSee('1,500,000.00');
    }
}
