<?php

namespace Tests\Unit\Models;

use App\Models\InventoryLog;
use App\Models\Material;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierOrderLog;
use App\Models\SupplierOrderMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierOrderModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: getStatusBadgeAttribute Branch Coverage across all statuses
     */
    public function test_order_status_badge_branches()
    {
        $statuses = [
            'pending' => 'Pending Approval',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'ready_for_delivery' => 'Ready for Delivery',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'custom_hold' => 'Custom hold',
        ];

        foreach ($statuses as $status => $expectedLabel) {
            $order = new SupplierOrder(['status' => $status]);
            $badge = $order->status_badge;
            $this->assertEquals($expectedLabel, $badge['label'], "Failed assertion for status badge: {$status}");
            $this->assertArrayHasKey('color', $badge);
            $this->assertArrayHasKey('bg', $badge);
            $this->assertArrayHasKey('border', $badge);
        }
    }

    /**
     * White-Box Test: syncToInventory() - Idempotency Guard
     * Path: If is_synced_to_inventory is already true, it must immediately return false.
     */
    public function test_sync_to_inventory_idempotency_guard()
    {
        $order = new SupplierOrder(['is_synced_to_inventory' => true]);
        $result = $order->syncToInventory();
        $this->assertFalse($result);
    }

    /**
     * White-Box Test: syncToInventory() - New Materials Creation with Category Code Prefixes
     * Branch 1: Category 'Windows & Doors' -> MAT-WNDR-
     * Branch 2: Category 'Roofing' -> MAT-ROOF-
     * Branch 3: Category 'Structural & Masonry' -> MAT-STRC-
     * Branch 4: Default Category -> MAT-SUP-
     */
    public function test_sync_to_inventory_creates_new_materials_with_category_prefixes()
    {
        $testCategories = [
            'Windows & Doors' => 'MAT-WNDR-',
            'Roofing' => 'MAT-ROOF-',
            'Structural & Masonry' => 'MAT-STRC-',
            'General Hardware' => 'MAT-SUP-',
        ];

        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.sync@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        foreach ($testCategories as $category => $expectedPrefix) {
            $supplier = Supplier::create([
                'name' => "Supplier for {$category}",
                'code' => 'SUP-' . uniqid(),
                'category' => $category,
                'email' => 'sup_' . uniqid() . '@example.test',
                'status' => 'active',
            ]);

            $order = SupplierOrder::create([
                'order_code' => 'ORD-SYNC-' . uniqid(),
                'supplier_id' => $supplier->id,
                'ordered_by_user_id' => $admin->id,
                'delivery_location' => 'Central Depot',
                'requested_delivery_date' => now()->addDays(2),
                'total_amount' => 5000,
                'status' => 'delivered',
            ]);

            $uniqueMatName = "Brand New {$category} Product " . uniqid();
            SupplierOrderItem::create([
                'supplier_order_id' => $order->id,
                'material_name' => $uniqueMatName,
                'quantity' => 20,
                'unit' => 'pcs',
                'unit_price' => 250.00,
                'total_price' => 5000.00,
            ]);

            $syncResult = $order->syncToInventory();
            $this->assertTrue($syncResult);

            $createdMat = Material::where('name', $uniqueMatName)->first();
            $this->assertNotNull($createdMat);
            $this->assertStringStartsWith($expectedPrefix, $createdMat->material_code);
            $this->assertEquals(20, $createdMat->stock_quantity);
            $this->assertEquals(250.00, $createdMat->unit_cost);
            $this->assertTrue($createdMat->is_new_product);

            // Verify InventoryLog creation
            $log = InventoryLog::where('reference_no', $order->order_code)->first();
            $this->assertNotNull($log);
            $this->assertEquals('restock', $log->transaction_type);
            $this->assertEquals(20, $log->quantity);
            $this->assertEquals($createdMat->id, $log->material_id);
            $this->assertStringContainsString('Central Warehouse Depot', $log->notes);

            // Verify order is marked as synced
            $order->refresh();
            $this->assertTrue($order->is_synced_to_inventory);
        }
    }

    /**
     * White-Box Test: syncToInventory() - Existing Material Stock Increment and Project Material BOM Sync
     * Path 1: Material already exists -> increments stock quantity
     * Path 2: project_id is provided -> creates / updates ProjectMaterial record
     */
    public function test_sync_to_inventory_increments_existing_stock_and_syncs_project_material()
    {
        $project = Project::create([
            'project_code' => 'PRJ-SYNC-01',
            'title' => 'Skyline Heights Residence',
            'client_name' => 'Alice Client',
            'location' => 'Quezon City',
            'land_area_sqm' => 250.0,
            'floor_area_sqm' => 320.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
            'contract_budget' => 500000,
            'spent_budget' => 100000,
        ]);

        $supplier = Supplier::create([
            'name' => 'Apex Roofing Supply',
            'code' => 'SUP-APEX-01',
            'category' => 'Roofing',
            'email' => 'apex.sync@example.test',
            'status' => 'active',
        ]);

        // Pre-create existing material in inventory
        $existingMaterial = Material::create([
            'material_code' => 'MAT-ROOF-EXISTING',
            'name' => 'Ridge Roll 0.4mm',
            'category' => 'Roofing',
            'unit' => 'pcs',
            'unit_cost' => 300.00,
            'stock_quantity' => 15,
            'is_new_product' => false,
        ]);

        $order = SupplierOrder::create([
            'order_code' => 'ORD-PRJ-001',
            'supplier_id' => $supplier->id,
            'project_id' => $project->id,
            'delivery_location' => 'Skyline Site',
            'requested_delivery_date' => now()->addDays(1),
            'total_amount' => 3500.00,
            'status' => 'delivered',
        ]);

        SupplierOrderItem::create([
            'supplier_order_id' => $order->id,
            'material_name' => 'Ridge Roll 0.4mm',
            'quantity' => 10,
            'unit' => 'pcs',
            'unit_price' => 350.00,
            'total_price' => 3500.00,
        ]);

        $synced = $order->syncToInventory();
        $this->assertTrue($synced);

        $existingMaterial->refresh();
        $this->assertEquals(25, $existingMaterial->stock_quantity); // 15 + 10
        $this->assertEquals(350.00, $existingMaterial->unit_cost);
        $this->assertTrue($existingMaterial->is_new_product);

        // Check ProjectMaterial BOM allocation
        $projectMat = ProjectMaterial::where('project_id', $project->id)
            ->where('material_id', $existingMaterial->id)
            ->first();
        $this->assertNotNull($projectMat);
        $this->assertEquals(10, $projectMat->allocated_qty);
        $this->assertEquals(350.00, $projectMat->unit_price);

        // Check InventoryLog tied to project
        $log = InventoryLog::where('reference_no', 'ORD-PRJ-001')->first();
        $this->assertEquals($project->id, $log->project_id);
        $this->assertStringContainsString('for site ' . $project->title, $log->notes);
    }

    /**
     * White-Box Test: SupplierOrder Relationships
     */
    public function test_supplier_order_relationships()
    {
        $supplier = Supplier::create([
            'name' => 'Supplier Co',
            'code' => 'SUP-REL',
            'email' => 'rel@example.test',
            'category' => 'Roofing',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Buyer',
            'email' => 'buyer@example.test',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $order = SupplierOrder::create([
            'order_code' => 'ORD-REL-01',
            'supplier_id' => $supplier->id,
            'ordered_by_user_id' => $user->id,
            'delivery_location' => 'Depot',
            'requested_delivery_date' => now()->addDays(3),
            'total_amount' => 1200.00,
            'status' => 'pending',
        ]);

        $item = SupplierOrderItem::create([
            'supplier_order_id' => $order->id,
            'material_name' => 'Gutter Flashing',
            'quantity' => 5,
            'unit' => 'pcs',
            'unit_price' => 240.00,
            'total_price' => 1200.00,
        ]);

        $orderLog = SupplierOrderLog::create([
            'supplier_order_id' => $order->id,
            'user_id' => $user->id,
            'from_status' => 'draft',
            'to_status' => 'pending',
            'comment' => 'Initial submission',
        ]);

        $orderMessage = SupplierOrderMessage::create([
            'supplier_order_id' => $order->id,
            'user_id' => $user->id,
            'sender_role' => 'admin',
            'message' => 'Please expedite delivery.',
            'is_read' => false,
        ]);

        $this->assertInstanceOf(Supplier::class, $order->supplier);
        $this->assertInstanceOf(User::class, $order->orderedBy);
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $order->logs);
        $this->assertCount(1, $order->messages);
    }
}
