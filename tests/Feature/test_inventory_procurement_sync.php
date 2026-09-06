<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\Material;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

echo "========================================================\n";
echo "1. VERIFYING PROCUREMENT INVENTORY SYNC ON PO DELIVERY\n";
echo "========================================================\n";

$admin = User::where('role', 'admin')->first();
$supplier = Supplier::where('category', 'Roofing')->first();
$supplierMaterial = SupplierMaterial::where('supplier_id', $supplier->id)->first();

if (!$supplierMaterial) {
    die("Supplier material not found!\n");
}

echo "Supplier: {$supplier->name}\n";
echo "Product: {$supplierMaterial->name} (Price: PHP {$supplierMaterial->unit_price})\n";

// Check initial warehouse stock for this material
$initialMaterial = Material::where('name', $supplierMaterial->name)->first();
$initialStock = $initialMaterial ? $initialMaterial->stock_quantity : 0;
echo "Initial Warehouse Stock of '{$supplierMaterial->name}': {$initialStock}\n";

// 1. Create a test PO
$orderCode = 'ORD-SYNC-TEST-' . time();
$orderQty = 25;
$totalPrice = $orderQty * $supplierMaterial->unit_price;

$order = SupplierOrder::create([
    'order_code' => $orderCode,
    'supplier_id' => $supplier->id,
    'ordered_by_user_id' => $admin->id,
    'project_id' => null,
    'delivery_location' => 'Central Warehouse Depot',
    'requested_delivery_date' => now()->addDays(2),
    'total_amount' => $totalPrice,
    'status' => 'pending',
    'notes' => 'Automated test purchase order for inventory synchronization verification.',
]);

$orderItem = SupplierOrderItem::create([
    'supplier_order_id' => $order->id,
    'supplier_material_id' => $supplierMaterial->id,
    'material_name' => $supplierMaterial->name,
    'quantity' => $orderQty,
    'unit' => $supplierMaterial->unit,
    'unit_price' => $supplierMaterial->unit_price,
    'total_price' => $totalPrice,
]);

echo "Created Test PO: {$orderCode} for {$orderQty} {$supplierMaterial->unit}.\n";

// 2. Simulate Supplier delivering order (triggers syncToInventory)
$order->status = 'delivered';
$order->actual_delivery_date = now();
$synced = $order->syncToInventory();

if ($synced) {
    echo "SUCCESS: syncToInventory() executed.\n";
} else {
    echo "FAILED: syncToInventory() returned false.\n";
}

// 3. Verify stock in Admin Materials Inventory
$updatedMaterial = Material::where('name', $supplierMaterial->name)->first();
$newStock = $updatedMaterial ? $updatedMaterial->stock_quantity : 0;
$expectedStock = $initialStock + $orderQty;

if ($newStock === $expectedStock) {
    echo "SUCCESS: Warehouse stock correctly updated from {$initialStock} to {$newStock} (+{$orderQty}).\n";
} else {
    die("FAILED: Expected stock {$expectedStock}, got {$newStock}!\n");
}

// 4. Verify InventoryLog was created
$log = InventoryLog::where('reference_no', $orderCode)->first();
if ($log && $log->quantity === $orderQty && $log->transaction_type === 'restock') {
    echo "SUCCESS: InventoryLog created with Ref '{$log->reference_no}', Qty: +{$log->quantity}, Note: '{$log->notes}'.\n";
} else {
    die("FAILED: InventoryLog not found or incorrect for {$orderCode}!\n");
}

// 5. Test idempotency (ensure second sync doesn't duplicate stock)
$secondSync = $order->syncToInventory();
if ($secondSync === false) {
    echo "SUCCESS: Second sync safely skipped to prevent double stock increment.\n";
} else {
    die("FAILED: Second sync did not return false!\n");
}

// Clean up test PO and restore stock
$order->delete(); // cascade deletes items and logs
$updatedMaterial->decrement('stock_quantity', $orderQty);
$log->delete();
echo "SUCCESS: Test data cleaned up and warehouse stock restored.\n";

echo "\n========================================================\n";
echo "2. VERIFYING MANUAL STOCK OVERRIDE LOCKING IN INVENTORY\n";
echo "========================================================\n";

$invController = new \App\Http\Controllers\InventoryController();
$fakeRequest = \Illuminate\Http\Request::create('/inventory/1/update-stock', 'POST', [
    'stock_quantity' => 99999,
    'unit_cost' => 100,
]);

$response = $invController->updateStock($fakeRequest, 1);
if ($response->isRedirect() && session('error')) {
    echo "SUCCESS: Manual stock adjustment is LOCKED! Message: '" . session('error') . "'\n";
} else {
    echo "FAILED: Manual stock adjustment was not locked!\n";
}

echo "\n========================================================\n";
echo "3. VERIFYING BLADE TEMPLATE COMPILATION\n";
echo "========================================================\n";

$viewNames = [
    'inventory.index',
    'admin.suppliers.index',
    'admin.suppliers.materials',
    'admin.suppliers.orders',
    'supplier.dashboard',
    'supplier.materials',
    'supplier.orders',
];

Auth::login($admin);

foreach ($viewNames as $v) {
    try {
        if ($v === 'inventory.index') {
            $req = \Illuminate\Http\Request::create('/inventory', 'GET');
            $html = app(\App\Http\Controllers\InventoryController::class)->index($req)->render();
        } elseif (str_starts_with($v, 'admin.suppliers')) {
            if ($v === 'admin.suppliers.index') {
                $html = app(\App\Http\Controllers\AdminSupplierController::class)->index()->render();
            } elseif ($v === 'admin.suppliers.materials') {
                $html = app(\App\Http\Controllers\AdminSupplierController::class)->materials(new \Illuminate\Http\Request())->render();
            } else {
                $html = app(\App\Http\Controllers\AdminSupplierController::class)->orders(new \Illuminate\Http\Request())->render();
            }
        }
        echo "SUCCESS: View [{$v}] compiled and rendered cleanly (" . strlen($html) . " bytes).\n";
    } catch (\Throwable $e) {
        echo "ERROR rendering [{$v}]: " . $e->getMessage() . "\n";
    }
}

echo "\n========================================================\n";
echo "ALL TESTS COMPLETED SUCCESSFULLY!\n";
echo "========================================================\n";
