<?php

namespace Tests\Unit\Models;

use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierMaterialModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: SupplierMaterial Casts and Mass Assignment
     */
    public function test_supplier_material_casts_and_attributes()
    {
        $supplier = Supplier::create([
            'name' => 'Roof Builders Depot',
            'code' => 'SUP-ROOF',
            'category' => 'Roofing',
            'email' => 'sales@roofdepot.test',
            'status' => 'active',
        ]);

        $material = SupplierMaterial::create([
            'supplier_id' => $supplier->id,
            'material_code' => 'SM-CORR-01',
            'name' => 'Corrugated Roof Sheet 0.5mm',
            'category' => 'Roofing',
            'subcategory' => 'Roof Sheets',
            'description' => 'Galvalume coated sheet',
            'specifications' => '0.50mm x 8ft',
            'unit' => 'pcs',
            'available_quantity' => 150,
            'unit_price' => 540.50,
            'min_order_qty' => 10,
            'availability_status' => 'available',
            'image_url' => 'https://example.com/roof.jpg',
            'is_active' => true,
        ]);

        $this->assertIsInt($material->available_quantity);
        $this->assertEquals(150, $material->available_quantity);
        $this->assertEquals('540.50', $material->unit_price);
        $this->assertIsInt($material->min_order_qty);
        $this->assertEquals(10, $material->min_order_qty);
        $this->assertTrue($material->is_active);
    }

    /**
     * White-Box Test: getStatusBadgeAttribute Branch Coverage
     * Path 1: is_active is false -> 'Unavailable'
     * Path 2: availability_status is 'unavailable' -> 'Unavailable'
     * Path 3: is_active is true AND availability_status is 'available' -> 'Available'
     */
    public function test_status_badge_attribute_branches()
    {
        $inactiveMat = new SupplierMaterial([
            'is_active' => false,
            'availability_status' => 'available',
        ]);
        $badge1 = $inactiveMat->status_badge;
        $this->assertEquals('Unavailable', $badge1['label']);
        $this->assertEquals('#94a3b8', $badge1['color']);

        $unavailableMat = new SupplierMaterial([
            'is_active' => true,
            'availability_status' => 'unavailable',
        ]);
        $badge2 = $unavailableMat->status_badge;
        $this->assertEquals('Unavailable', $badge2['label']);
        $this->assertEquals('#94a3b8', $badge2['color']);

        $availableMat = new SupplierMaterial([
            'is_active' => true,
            'availability_status' => 'available',
        ]);
        $badge3 = $availableMat->status_badge;
        $this->assertEquals('Available', $badge3['label']);
        $this->assertEquals('#10b981', $badge3['color']);
    }

    /**
     * White-Box Test: SupplierMaterial Relationships (supplier, orderItems)
     */
    public function test_supplier_material_relationships()
    {
        $supplier = Supplier::create([
            'name' => 'Window World',
            'code' => 'SUP-WIN',
            'category' => 'Windows & Doors',
            'email' => 'sales@windowworld.test',
            'status' => 'active',
        ]);

        $material = SupplierMaterial::create([
            'supplier_id' => $supplier->id,
            'material_code' => 'SM-ALUM-WIN',
            'name' => 'Aluminum Sliding Window 120x120',
            'category' => 'Windows & Doors',
            'unit' => 'sets',
            'unit_price' => 3800.00,
            'is_active' => true,
        ]);

        $order = SupplierOrder::create([
            'order_code' => 'ORD-WW-001',
            'supplier_id' => $supplier->id,
            'delivery_location' => 'Depot 1',
            'requested_delivery_date' => now()->addDays(5),
            'total_amount' => 7600.00,
            'status' => 'confirmed',
        ]);

        $orderItem = SupplierOrderItem::create([
            'supplier_order_id' => $order->id,
            'supplier_material_id' => $material->id,
            'material_name' => $material->name,
            'quantity' => 2,
            'unit' => 'sets',
            'unit_price' => 3800.00,
            'total_price' => 7600.00,
        ]);

        $this->assertInstanceOf(Supplier::class, $material->supplier);
        $this->assertEquals($supplier->id, $material->supplier->id);

        $this->assertCount(1, $material->orderItems);
        $this->assertEquals($orderItem->id, $material->orderItems->first()->id);
    }
}
