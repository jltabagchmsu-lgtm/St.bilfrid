<?php

namespace Tests\Unit\Models;

use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierNotification;
use App\Models\SupplierOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: Supplier Model Attributes and Rating Cast
     */
    public function test_supplier_attributes_and_casting()
    {
        $supplier = Supplier::create([
            'name' => 'Titan Steel Corp',
            'code' => 'SUP-TITAN',
            'category' => 'Structural & Masonry',
            'contact_person' => 'Bob Builder',
            'email' => 'contact@titansteel.test',
            'phone' => '+639123456789',
            'address' => '123 Industrial Ave, Manila',
            'rating' => 4.85,
            'status' => 'active',
        ]);

        $this->assertEquals('Titan Steel Corp', $supplier->name);
        $this->assertEquals('SUP-TITAN', $supplier->code);
        $this->assertEquals('Structural & Masonry', $supplier->category);
        $this->assertEquals('4.85', $supplier->rating);
        $this->assertEquals('active', $supplier->status);
    }

    /**
     * White-Box Test: isActive() Method Branch Coverage
     * Path 1: status is 'active' -> true
     * Path 2: status is 'inactive' -> false
     * Path 3: status is null/other -> false
     */
    public function test_is_active_method_branches()
    {
        $activeSupplier = new Supplier(['status' => 'active']);
        $this->assertTrue($activeSupplier->isActive());

        $inactiveSupplier = new Supplier(['status' => 'inactive']);
        $this->assertFalse($inactiveSupplier->isActive());

        $otherSupplier = new Supplier(['status' => 'suspended']);
        $this->assertFalse($otherSupplier->isActive());
    }

    /**
     * White-Box Test: getCategoryColorAttribute Branch Coverage
     * Path 1: category is 'Windows & Doors' -> '#38bdf8'
     * Path 2: category is 'Roofing' -> '#ef4444'
     * Path 3: category is 'Structural & Masonry' -> '#10b981'
     * Path 4: default category -> '#818cf8'
     */
    public function test_category_color_attribute_branches()
    {
        $winSupplier = new Supplier(['category' => 'Windows & Doors']);
        $this->assertEquals('#38bdf8', $winSupplier->category_color);

        $roofSupplier = new Supplier(['category' => 'Roofing']);
        $this->assertEquals('#ef4444', $roofSupplier->category_color);

        $structSupplier = new Supplier(['category' => 'Structural & Masonry']);
        $this->assertEquals('#10b981', $structSupplier->category_color);

        $defaultSupplier = new Supplier(['category' => 'Plumbing & Fixtures']);
        $this->assertEquals('#818cf8', $defaultSupplier->category_color);
    }

    /**
     * White-Box Test: Supplier Relationships (users, materials, activeMaterials, orders, notifications)
     */
    public function test_supplier_relationships_and_active_materials_scope()
    {
        $supplier = Supplier::create([
            'name' => 'Metro Cement Supply',
            'code' => 'SUP-METRO',
            'category' => 'Structural & Masonry',
            'email' => 'sales@metrocement.test',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Metro Staff',
            'email' => 'staff@metrocement.test',
            'role' => 'supplier',
            'supplier_id' => $supplier->id,
            'password' => bcrypt('password'),
        ]);

        $activeMat = SupplierMaterial::create([
            'supplier_id' => $supplier->id,
            'material_code' => 'SM-CEM-01',
            'name' => 'Portland Cement Type 1',
            'category' => 'Structural & Masonry',
            'unit' => 'bags',
            'unit_price' => 240.00,
            'is_active' => true,
        ]);

        $inactiveMat = SupplierMaterial::create([
            'supplier_id' => $supplier->id,
            'material_code' => 'SM-CEM-02',
            'name' => 'Discontinued White Cement',
            'category' => 'Structural & Masonry',
            'unit' => 'bags',
            'unit_price' => 310.00,
            'is_active' => false,
        ]);

        $order = SupplierOrder::create([
            'order_code' => 'ORD-TEST-001',
            'supplier_id' => $supplier->id,
            'delivery_location' => 'Main Yard',
            'requested_delivery_date' => now()->addDays(3),
            'total_amount' => 5000.00,
            'status' => 'pending',
        ]);

        $notification = SupplierNotification::create([
            'supplier_id' => $supplier->id,
            'type' => 'order_created',
            'title' => 'New Order Received',
            'message' => 'You received order ORD-TEST-001',
        ]);

        $this->assertCount(1, $supplier->users);
        $this->assertEquals($user->id, $supplier->users->first()->id);

        $this->assertCount(2, $supplier->materials);
        $this->assertCount(1, $supplier->activeMaterials);
        $this->assertEquals($activeMat->id, $supplier->activeMaterials->first()->id);

        $this->assertCount(1, $supplier->orders);
        $this->assertEquals($order->id, $supplier->orders->first()->id);

        $this->assertCount(1, $supplier->notifications);
        $this->assertEquals($notification->id, $supplier->notifications->first()->id);
    }
}
