<?php

namespace Tests\Unit\Models;

use App\Models\Supplier;
use App\Models\SupplierInquiry;
use App\Models\SupplierMaterial;
use App\Models\SupplierNotification;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierOrderLog;
use App\Models\SupplierOrderMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierAuxiliaryModelsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: SupplierOrderItem Casts and Relationships
     */
    public function test_supplier_order_item_casts_and_relationships()
    {
        $supplier = Supplier::create([
            'name' => 'Glass & Aluminum',
            'code' => 'SUP-GLAL',
            'email' => 'sales@glal.test',
            'category' => 'Windows & Doors',
            'status' => 'active',
        ]);

        $material = SupplierMaterial::create([
            'supplier_id' => $supplier->id,
            'material_code' => 'SM-GL-01',
            'name' => 'Tempered Glass Panel',
            'category' => 'Windows & Doors',
            'unit' => 'pcs',
            'unit_price' => 1500.00,
        ]);

        $order = SupplierOrder::create([
            'order_code' => 'ORD-AUX-001',
            'supplier_id' => $supplier->id,
            'delivery_location' => 'Yard A',
            'requested_delivery_date' => now()->addDays(4),
            'total_amount' => 4500.00,
            'status' => 'pending',
        ]);

        $item = SupplierOrderItem::create([
            'supplier_order_id' => $order->id,
            'supplier_material_id' => $material->id,
            'material_name' => $material->name,
            'quantity' => 3,
            'unit' => 'pcs',
            'unit_price' => 1500.00,
            'total_price' => 4500.00,
        ]);

        $this->assertIsInt($item->quantity);
        $this->assertEquals('1500.00', $item->unit_price);
        $this->assertEquals('4500.00', $item->total_price);
        $this->assertInstanceOf(SupplierOrder::class, $item->order);
        $this->assertInstanceOf(SupplierMaterial::class, $item->material);
    }

    /**
     * White-Box Test: SupplierOrderLog and SupplierOrderMessage Relationships
     */
    public function test_supplier_order_log_and_message_models()
    {
        $supplier = Supplier::create([
            'name' => 'Roof Depot',
            'code' => 'SUP-ROOF-AUX',
            'email' => 'sales@roofaux.test',
            'category' => 'Roofing',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Officer Jane',
            'email' => 'jane@example.test',
            'role' => 'roofing_transfer',
            'password' => bcrypt('password'),
        ]);

        $order = SupplierOrder::create([
            'order_code' => 'ORD-AUX-002',
            'supplier_id' => $supplier->id,
            'delivery_location' => 'Warehouse',
            'requested_delivery_date' => now()->addDays(2),
            'total_amount' => 1000.00,
            'status' => 'pending',
        ]);

        $log = SupplierOrderLog::create([
            'supplier_order_id' => $order->id,
            'user_id' => $user->id,
            'from_status' => 'pending',
            'to_status' => 'confirmed',
            'comment' => 'Approved by procurement team',
        ]);

        $msg = SupplierOrderMessage::create([
            'supplier_order_id' => $order->id,
            'user_id' => $user->id,
            'sender_role' => 'admin',
            'message' => 'Please confirm dispatch schedule.',
            'attachment_url' => 'https://example.com/spec.pdf',
            'is_read' => false,
        ]);

        $this->assertInstanceOf(SupplierOrder::class, $log->order);
        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals('pending', $log->from_status);
        $this->assertEquals('confirmed', $log->to_status);

        $this->assertInstanceOf(SupplierOrder::class, $msg->order);
        $this->assertInstanceOf(User::class, $msg->user);
        $this->assertFalse($msg->is_read);
        $this->assertIsBool($msg->is_read);
    }

    /**
     * White-Box Test: SupplierNotification and SupplierInquiry Models
     */
    public function test_supplier_notification_and_inquiry_models()
    {
        $supplier = Supplier::create([
            'name' => 'Inquiry Partner',
            'code' => 'SUP-INQ',
            'email' => 'inq@example.test',
            'category' => 'Structural & Masonry',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Project Engineer',
            'email' => 'engineer@example.test',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $material = SupplierMaterial::create([
            'supplier_id' => $supplier->id,
            'material_code' => 'SM-INQ-MAT',
            'name' => 'High Tensile Rebar',
            'category' => 'Structural & Masonry',
            'unit' => 'pcs',
            'unit_price' => 600.00,
        ]);

        $notification = SupplierNotification::create([
            'supplier_id' => $supplier->id,
            'user_id' => $user->id,
            'type' => 'new_inquiry',
            'title' => 'Price Quote Requested',
            'message' => 'Client requested quote for 100 pcs rebar',
            'is_read' => true,
            'link' => '/supplier/inquiries/1',
        ]);

        $inquiry = SupplierInquiry::create([
            'supplier_id' => $supplier->id,
            'supplier_material_id' => $material->id,
            'user_id' => $user->id,
            'subject' => 'Bulk Discount Inquiry',
            'message' => 'Can you quote 500 pcs?',
            'requested_quantity' => 500,
            'status' => 'quoted',
            'supplier_response' => 'Discounted price offered.',
            'quoted_unit_price' => 550.00,
            'responded_at' => now(),
        ]);

        $this->assertTrue($notification->is_read);
        $this->assertInstanceOf(Supplier::class, $notification->supplier);
        $this->assertInstanceOf(User::class, $notification->user);

        $this->assertInstanceOf(Supplier::class, $inquiry->supplier);
        $this->assertInstanceOf(SupplierMaterial::class, $inquiry->material);
        $this->assertInstanceOf(User::class, $inquiry->user);
        $this->assertEquals(500, $inquiry->requested_quantity);
        $this->assertEquals('550.00', $inquiry->quoted_unit_price);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $inquiry->responded_at);
    }
}
