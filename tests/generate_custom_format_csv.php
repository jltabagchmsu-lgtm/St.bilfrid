<?php

/**
 * Format White-Box Test Results to User's Specific Column Schema:
 * Test Case ID | Use Case | Tested Code Segment | Test Description | Input Values | Expected Behavior | Actual Behavior | Result | Duration
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DailyMaterialUsage;
use App\Models\InventoryLog;
use App\Models\Material;
use App\Models\Payment;
use App\Models\Personnel;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectMaterial;
use App\Models\ProjectMaterialTransfer;
use App\Models\ProjectPhoto;
use App\Models\ProjectScopeItem;
use App\Models\ProjectScopeLine;
use App\Models\ProjectTask;
use App\Models\ProjectTaskMaterial;
use App\Models\ServiceRequest;
use App\Models\Supplier;
use App\Models\SupplierInquiry;
use App\Models\SupplierMaterial;
use App\Models\SupplierNotification;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierOrderLog;
use App\Models\SupplierOrderMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$testCases = [];
$counter = 1;

function addTest(&$testCases, &$counter, $useCase, $segment, $description, $inputs, $expected, $actualBehaviorEvaluator) {
    $startTime = microtime(true);
    $result = 'Pass';
    $actualObserved = '';
    
    try {
        $eval = $actualBehaviorEvaluator();
        if ($eval === false) {
            $result = 'Fail';
            $actualObserved = 'Failed assertion or condition mismatch';
        } else {
            $actualObserved = is_string($eval) ? $eval : 'Executed successfully and verified exact internal state';
        }
    } catch (\Throwable $e) {
        $result = 'Fail';
        $actualObserved = 'Exception caught: ' . $e->getMessage();
    }
    
    $durationMs = max(1, round((microtime(true) - $startTime) * 1000));
    $tcId = 'TC-' . str_pad($counter++, 3, '0', STR_PAD_LEFT);
    
    $testCases[] = [
        'Test Case ID' => $tcId,
        'Use Case' => $useCase,
        'Tested Code Segment' => $segment,
        'Test Description' => $description,
        'Input Values' => $inputs,
        'Expected Behavior' => $expected,
        'Actual Behavior' => $actualObserved,
        'Result' => $result,
        'Duration' => $durationMs . ' ms',
    ];
}

DB::beginTransaction();

try {
    // -------------------------------------------------------------
    // USER AUTHENTICATION & ACCESS CONTROL
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'User Authentication & Roles', 'Model Method', 'User isAdmin() should evaluate empty role to true as fallback admin', 'role = null', 'Should return true for master admin default', function() {
        $u = new User(['role' => null]);
        return $u->isAdmin() ? 'Evaluated to true as master administrator fallback' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Model Method', 'User isAdmin() should evaluate explicit admin role to true', 'role = "admin"', 'Should return true for explicit admin role', function() {
        $u = new User(['role' => 'admin']);
        return $u->isAdmin() ? 'Successfully identified admin authorization' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Model Method', 'User isAdmin() should evaluate non-admin role to false', 'role = "roofing_transfer"', 'Should return false for non-admin role', function() {
        $u = new User(['role' => 'roofing_transfer']);
        return !$u->isAdmin() ? 'Correctly denied administrative access' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Model Method', 'User isRoofingOfficer() should identify roofing portal officer', 'role = "roofing_transfer"', 'Should return true for roofing officer', function() {
        $u = new User(['role' => 'roofing_transfer']);
        return $u->isRoofingOfficer() ? 'Identified roofing transfer officer correctly' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Model Method', 'User isRoofingOfficer() should reject non-roofing role', 'role = "admin"', 'Should return false for admin role', function() {
        $u = new User(['role' => 'admin']);
        return !$u->isRoofingOfficer() ? 'Properly restricted roofing transfer scope' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Model Method', 'User isWindowsDoorsOfficer() should identify windows & doors officer', 'role = "windows_doors_transfer"', 'Should return true for windows officer', function() {
        $u = new User(['role' => 'windows_doors_transfer']);
        return $u->isWindowsDoorsOfficer() ? 'Identified windows & doors officer correctly' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Model Method', 'User isWindowsDoorsOfficer() should reject non-windows role', 'role = "roofing_transfer"', 'Should return false for roofing role', function() {
        $u = new User(['role' => 'roofing_transfer']);
        return !$u->isWindowsDoorsOfficer() ? 'Properly restricted windows & doors scope' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Model Method', 'User isSupplier() should identify user with supplier role', 'role = "supplier", supplier_id = null', 'Should return true for supplier role', function() {
        $u = new User(['role' => 'supplier', 'supplier_id' => null]);
        return $u->isSupplier() ? 'Recognized external trade supplier account' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Model Method', 'User isSupplier() should identify user with linked supplier_id', 'role = null, supplier_id = 15', 'Should return true for linked supplier ID', function() {
        $u = new User(['role' => null, 'supplier_id' => 15]);
        return $u->isSupplier() ? 'Recognized supplier affiliation via ID' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Model Method', 'User isSupplier() should return false for internal personnel', 'role = "admin", supplier_id = null', 'Should return false for internal personnel', function() {
        $u = new User(['role' => 'admin', 'supplier_id' => null]);
        return !$u->isSupplier() ? 'Properly verified internal non-supplier user' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Accessor', 'User role_title should format supplier company name and category', 'supplier={name: "Apex Steel", category: "Structural & Masonry"}', 'Should return "Apex Steel (Structural & Masonry)"', function() {
        $sup = new Supplier(['name' => 'Apex Steel', 'category' => 'Structural & Masonry']);
        $u = new User(['role' => 'supplier']);
        $u->setRelation('supplier', $sup);
        return $u->role_title === 'Apex Steel (Structural & Masonry)' ? 'Formatted full supplier company title with trade category' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Accessor', 'User role_title fallback for supplier without relation', 'supplier = null, role = "supplier"', 'Should return "Supplier Account"', function() {
        $u = new User(['role' => 'supplier']);
        return $u->role_title === 'Supplier Account' ? 'Returned generic Supplier Account fallback title' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Accessor', 'User role_title should return Roofing Transfer Officer title', 'role = "roofing_transfer"', 'Should return "Roofing Transfer Officer"', function() {
        $u = new User(['role' => 'roofing_transfer']);
        return $u->role_title === 'Roofing Transfer Officer' ? 'Returned exact Roofing Transfer Officer designation' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Accessor', 'User role_title should return Windows & Doors Transfer Officer title', 'role = "windows_doors_transfer"', 'Should return "Windows & Doors Transfer Officer"', function() {
        $u = new User(['role' => 'windows_doors_transfer']);
        return $u->role_title === 'Windows & Doors Transfer Officer' ? 'Returned exact Windows & Doors Officer designation' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Accessor', 'User role_title should return Master Administrator title', 'role = "admin" or null', 'Should return "Master Administrator"', function() {
        $u = new User(['role' => 'admin']);
        return $u->role_title === 'Master Administrator' ? 'Returned Master Administrator title' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Accessor', 'User portal_route should route supplier to supplier dashboard', 'role = "supplier"', 'Should return supplier.dashboard route URL', function() {
        $u = new User(['role' => 'supplier']);
        return $u->portal_route === route('supplier.dashboard') ? 'Resolved supplier portal dashboard route cleanly' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Accessor', 'User portal_route should route roofing officer to roofing transfer index', 'role = "roofing_transfer"', 'Should return roofing.index route URL', function() {
        $u = new User(['role' => 'roofing_transfer']);
        return $u->portal_route === route('roofing.index') ? 'Resolved roofing transfer portal route URL' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Accessor', 'User portal_route should route windows officer to windows transfer index', 'role = "windows_doors_transfer"', 'Should return windowsDoors.index route URL', function() {
        $u = new User(['role' => 'windows_doors_transfer']);
        return $u->portal_route === route('windowsDoors.index') ? 'Resolved windows & doors transfer portal route URL' : false;
    });

    addTest($testCases, $counter, 'User Authentication & Roles', 'Accessor', 'User portal_route should route admin to main system dashboard', 'role = "admin"', 'Should return main dashboard route URL', function() {
        $u = new User(['role' => 'admin']);
        return $u->portal_route === route('dashboard') ? 'Resolved master admin landing dashboard route' : false;
    });

    // -------------------------------------------------------------
    // TRADE SUPPLIER & CATALOG MANAGEMENT
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'Supplier & Catalog Management', 'Model Method', 'Supplier isActive() should return true for active status', 'status = "active"', 'Should evaluate to true', function() {
        $s = new Supplier(['status' => 'active']);
        return $s->isActive() ? 'Confirmed active supplier standing' : false;
    });

    addTest($testCases, $counter, 'Supplier & Catalog Management', 'Model Method', 'Supplier isActive() should return false for inactive status', 'status = "inactive"', 'Should evaluate to false', function() {
        $s = new Supplier(['status' => 'inactive']);
        return !$s->isActive() ? 'Flagged inactive supplier status correctly' : false;
    });

    addTest($testCases, $counter, 'Supplier & Catalog Management', 'Accessor', 'Supplier category_color should return sky blue for Windows & Doors', 'category = "Windows & Doors"', 'Should return #38bdf8', function() {
        $s = new Supplier(['category' => 'Windows & Doors']);
        return $s->category_color === '#38bdf8' ? 'Mapped category badge color to sky blue (#38bdf8)' : false;
    });

    addTest($testCases, $counter, 'Supplier & Catalog Management', 'Accessor', 'Supplier category_color should return red for Roofing', 'category = "Roofing"', 'Should return #ef4444', function() {
        $s = new Supplier(['category' => 'Roofing']);
        return $s->category_color === '#ef4444' ? 'Mapped category badge color to roofing red (#ef4444)' : false;
    });

    addTest($testCases, $counter, 'Supplier & Catalog Management', 'Accessor', 'Supplier category_color should return emerald for Structural & Masonry', 'category = "Structural & Masonry"', 'Should return #10b981', function() {
        $s = new Supplier(['category' => 'Structural & Masonry']);
        return $s->category_color === '#10b981' ? 'Mapped category badge color to emerald (#10b981)' : false;
    });

    addTest($testCases, $counter, 'Supplier & Catalog Management', 'Accessor', 'Supplier category_color should return indigo for default category', 'category = "Plumbing"', 'Should return #818cf8', function() {
        $s = new Supplier(['category' => 'Plumbing']);
        return $s->category_color === '#818cf8' ? 'Mapped category badge color to default indigo (#818cf8)' : false;
    });

    addTest($testCases, $counter, 'Supplier & Catalog Management', 'Accessor', 'SupplierMaterial status_badge should return Unavailable when inactive', 'is_active = false, availability_status = "available"', 'Should return Unavailable badge', function() {
        $sm = new SupplierMaterial(['is_active' => false, 'availability_status' => 'available']);
        return $sm->status_badge['label'] === 'Unavailable' ? 'Correctly flagged inactive product as Unavailable' : false;
    });

    addTest($testCases, $counter, 'Supplier & Catalog Management', 'Accessor', 'SupplierMaterial status_badge should return Unavailable when status is unavailable', 'is_active = true, availability_status = "unavailable"', 'Should return Unavailable badge', function() {
        $sm = new SupplierMaterial(['is_active' => true, 'availability_status' => 'unavailable']);
        return $sm->status_badge['label'] === 'Unavailable' ? 'Correctly flagged out of stock item as Unavailable' : false;
    });

    addTest($testCases, $counter, 'Supplier & Catalog Management', 'Accessor', 'SupplierMaterial status_badge should return Available for active available item', 'is_active = true, availability_status = "available"', 'Should return Available badge', function() {
        $sm = new SupplierMaterial(['is_active' => true, 'availability_status' => 'available']);
        return $sm->status_badge['label'] === 'Available' ? 'Displayed active Available status badge' : false;
    });

    // -------------------------------------------------------------
    // PURCHASE ORDERS & INVENTORY SYNCHRONIZATION
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'Purchase Orders & Inventory Sync', 'Accessor', 'SupplierOrder status_badge should format pending status', 'status = "pending"', 'Should return Pending Approval (#f59e0b)', function() {
        $so = new SupplierOrder(['status' => 'pending']);
        return $so->status_badge['label'] === 'Pending Approval' ? 'Formatted pending PO badge with amber styling' : false;
    });

    addTest($testCases, $counter, 'Purchase Orders & Inventory Sync', 'Accessor', 'SupplierOrder status_badge should format confirmed status', 'status = "confirmed"', 'Should return Confirmed (#38bdf8)', function() {
        $so = new SupplierOrder(['status' => 'confirmed']);
        return $so->status_badge['label'] === 'Confirmed' ? 'Formatted confirmed PO badge with sky blue styling' : false;
    });

    addTest($testCases, $counter, 'Purchase Orders & Inventory Sync', 'Accessor', 'SupplierOrder status_badge should format processing status', 'status = "processing"', 'Should return Processing (#818cf8)', function() {
        $so = new SupplierOrder(['status' => 'processing']);
        return $so->status_badge['label'] === 'Processing' ? 'Formatted processing PO badge with indigo styling' : false;
    });

    addTest($testCases, $counter, 'Purchase Orders & Inventory Sync', 'Accessor', 'SupplierOrder status_badge should format ready_for_delivery status', 'status = "ready_for_delivery"', 'Should return Ready for Delivery (#ec4899)', function() {
        $so = new SupplierOrder(['status' => 'ready_for_delivery']);
        return $so->status_badge['label'] === 'Ready for Delivery' ? 'Formatted delivery dispatch ready badge' : false;
    });

    addTest($testCases, $counter, 'Purchase Orders & Inventory Sync', 'Accessor', 'SupplierOrder status_badge should format delivered status', 'status = "delivered"', 'Should return Delivered (#10b981)', function() {
        $so = new SupplierOrder(['status' => 'delivered']);
        return $so->status_badge['label'] === 'Delivered' ? 'Formatted delivered badge with emerald styling' : false;
    });

    addTest($testCases, $counter, 'Purchase Orders & Inventory Sync', 'Accessor', 'SupplierOrder status_badge should format completed status', 'status = "completed"', 'Should return Completed (#22c55e)', function() {
        $so = new SupplierOrder(['status' => 'completed']);
        return $so->status_badge['label'] === 'Completed' ? 'Formatted completed PO badge with green styling' : false;
    });

    addTest($testCases, $counter, 'Purchase Orders & Inventory Sync', 'Accessor', 'SupplierOrder status_badge should format cancelled status', 'status = "cancelled"', 'Should return Cancelled (#ef4444)', function() {
        $so = new SupplierOrder(['status' => 'cancelled']);
        return $so->status_badge['label'] === 'Cancelled' ? 'Formatted cancelled PO badge with danger red styling' : false;
    });

    addTest($testCases, $counter, 'Purchase Orders & Inventory Sync', 'Model Method', 'SupplierOrder syncToInventory() idempotency protection against double execution', 'is_synced_to_inventory = true', 'Should return false and skip inventory modification', function() {
        $so = new SupplierOrder(['is_synced_to_inventory' => true]);
        return $so->syncToInventory() === false ? 'Safely bypassed sync to prevent double stock incrementation' : false;
    });

    addTest($testCases, $counter, 'Purchase Orders & Inventory Sync', 'Model Method', 'SupplierOrder syncToInventory() execution creates central stock & logs', 'Supplier PO items delivered', 'Should create Material, increment stock, and write InventoryLog', function() {
        $supplier = Supplier::create([
            'name' => 'Qase Sync Supplier ' . uniqid(),
            'code' => 'SUP-QASE-' . uniqid(),
            'email' => 'qase_' . uniqid() . '@example.test',
            'category' => 'Roofing',
            'status' => 'active',
        ]);
        $order = SupplierOrder::create([
            'order_code' => 'ORD-QASE-' . uniqid(),
            'supplier_id' => $supplier->id,
            'delivery_location' => 'Central Depot',
            'requested_delivery_date' => now()->addDays(2),
            'total_amount' => 4500,
            'status' => 'delivered',
        ]);
        $matName = 'Qase Roof Sheet ' . uniqid();
        SupplierOrderItem::create([
            'supplier_order_id' => $order->id,
            'material_name' => $matName,
            'quantity' => 15,
            'unit' => 'pcs',
            'unit_price' => 300.00,
            'total_price' => 4500.00,
        ]);
        $res = $order->syncToInventory();
        $createdMat = Material::where('name', $matName)->first();
        return ($res === true && $createdMat && $createdMat->stock_quantity === 15) ? 'Delivered PO products synced to Material inventory and logged' : false;
    });

    // -------------------------------------------------------------
    // WAREHOUSE & INVENTORY MOVEMENTS
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'Warehouse & Inventory Movements', 'Accessor', 'InventoryLog transaction_badge formats excess return movements', 'transaction_type = "excess_return"', 'Should return Excess Material Returned', function() {
        $log = new InventoryLog(['transaction_type' => 'excess_return']);
        return $log->transaction_badge['label'] === 'Excess Material Returned' ? 'Formatted Excess Material Returned movement badge' : false;
    });

    addTest($testCases, $counter, 'Warehouse & Inventory Movements', 'Accessor', 'InventoryLog transaction_badge formats site BOM allocations', 'transaction_type = "allocation"', 'Should return Site BOM Allocation', function() {
        $log = new InventoryLog(['transaction_type' => 'allocation']);
        return $log->transaction_badge['label'] === 'Site BOM Allocation' ? 'Formatted Site BOM Allocation badge' : false;
    });

    addTest($testCases, $counter, 'Warehouse & Inventory Movements', 'Accessor', 'InventoryLog transaction_badge formats daily consumption usage', 'transaction_type = "usage"', 'Should return Site Consumption Recorded', function() {
        $log = new InventoryLog(['transaction_type' => 'usage']);
        return $log->transaction_badge['label'] === 'Site Consumption Recorded' ? 'Formatted Site Consumption Recorded badge' : false;
    });

    addTest($testCases, $counter, 'Warehouse & Inventory Movements', 'Accessor', 'InventoryLog transaction_badge formats PO restock deliveries', 'transaction_type = "restock"', 'Should return Warehouse Restock / PO', function() {
        $log = new InventoryLog(['transaction_type' => 'restock']);
        return $log->transaction_badge['label'] === 'Warehouse Restock / PO' ? 'Formatted Warehouse Restock PO badge' : false;
    });

    addTest($testCases, $counter, 'Warehouse & Inventory Movements', 'Accessor', 'InventoryLog transaction_badge formats manual inventory adjustments', 'transaction_type = "adjustment"', 'Should return Inventory Adjustment', function() {
        $log = new InventoryLog(['transaction_type' => 'adjustment']);
        return $log->transaction_badge['label'] === 'Inventory Adjustment' ? 'Formatted general Inventory Adjustment fallback badge' : false;
    });

    // -------------------------------------------------------------
    // BILLING & FINANCIAL TRANCHES
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment receipt_url handles null receipt file', 'receipt_file = null', 'Should return null', function() {
        $p = new Payment(['receipt_file' => null]);
        return $p->receipt_url === null ? 'Returned null cleanly for absent receipt attachment' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment receipt_url preserves fully-qualified HTTPS URLs', 'receipt_file = "https://cdn.example.com/receipt.pdf"', 'Should return unchanged URL', function() {
        $p = new Payment(['receipt_file' => 'https://cdn.example.com/receipt.pdf']);
        return $p->receipt_url === 'https://cdn.example.com/receipt.pdf' ? 'Preserved remote HTTPS storage CDN URL' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment receipt_url preserves absolute root paths', 'receipt_file = "/storage/receipts/doc.pdf"', 'Should return unchanged root path', function() {
        $p = new Payment(['receipt_file' => '/storage/receipts/doc.pdf']);
        return $p->receipt_url === '/storage/receipts/doc.pdf' ? 'Preserved absolute root path cleanly' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment receipt_url prepends /uploads/receipts/ to relative filenames', 'receipt_file = "or_receipt_001.jpg"', 'Should return /uploads/receipts/or_receipt_001.jpg', function() {
        $p = new Payment(['receipt_file' => 'or_receipt_001.jpg']);
        return $p->receipt_url === '/uploads/receipts/or_receipt_001.jpg' ? 'Prepended standard receipt storage directory' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment effective_or_number returns explicit official receipt number', 'official_receipt_no = "OR-2026-7777"', 'Should return "OR-2026-7777"', function() {
        $p = new Payment(['official_receipt_no' => 'OR-2026-7777', 'payment_date' => now()]);
        return $p->effective_or_number === 'OR-2026-7777' ? 'Returned registered official receipt number' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment effective_or_number generates formatted fallback OR string', 'official_receipt_no = null, date="2026-09-12", id=8', 'Should return "OR-202609-0008"', function() {
        $p = new Payment(['official_receipt_no' => null, 'payment_date' => \Carbon\Carbon::parse('2026-09-12')]);
        $p->id = 8;
        return $p->effective_or_number === 'OR-202609-0008' ? 'Generated padded sequential OR fallback code' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment financing_type_label returns Bank Construction Loan', 'financing_type = "bank_loan"', 'Should return Bank Construction Loan', function() {
        $p = new Payment(['financing_type' => 'bank_loan']);
        return $p->financing_type_label === 'Bank Construction Loan' ? 'Mapped bank financing label correctly' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment financing_type_label returns Pag-IBIG (HDMF) Loan', 'financing_type = "pagibig_loan"', 'Should return Pag-IBIG (HDMF) Loan', function() {
        $p = new Payment(['financing_type' => 'pagibig_loan']);
        return $p->financing_type_label === 'Pag-IBIG (HDMF) Loan' ? 'Mapped Pag-IBIG institutional loan label' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment financing_type_label returns Client Direct Equity', 'financing_type = "client_equity"', 'Should return Client Direct Equity', function() {
        $p = new Payment(['financing_type' => 'client_equity']);
        return $p->financing_type_label === 'Client Direct Equity' ? 'Mapped client direct equity label' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment financing_type_label returns Direct Progress Cash', 'financing_type = "cash_progress"', 'Should return Direct Progress Cash', function() {
        $p = new Payment(['financing_type' => 'cash_progress']);
        return $p->financing_type_label === 'Direct Progress Cash' ? 'Mapped direct cash progress payment label' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment construction_clearance_badge authorizes construction when paid', 'status = "paid"', 'Should return cleared=true and Authorized to Construct label', function() {
        $p = new Payment(['status' => 'paid']);
        return ($p->construction_clearance_badge['cleared'] === true && str_contains($p->construction_clearance_badge['label'], 'Authorized to Construct')) ? 'Authorized on-site construction works based on cleared payment' : false;
    });

    addTest($testCases, $counter, 'Billing & Financial Tranches', 'Accessor', 'Payment construction_clearance_badge indicates bank inspection scheduled', 'status = "pending", clearance_status = "inspection_scheduled"', 'Should return cleared=false and inspection scheduled message', function() {
        $p = new Payment(['status' => 'pending', 'construction_clearance_status' => 'inspection_scheduled']);
        return ($p->construction_clearance_badge['cleared'] === false && str_contains($p->construction_clearance_badge['label'], 'Inspection Scheduled')) ? 'Flagged bank/HDMF site inspection in progress' : false;
    });

    // -------------------------------------------------------------
    // PRC PERSONNEL & COMPLIANCE
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'PRC Personnel & Compliance', 'Model Method', 'Personnel isLicenseExpired() identifies expired license status', 'license_status = "expired"', 'Should evaluate to true', function() {
        $pers = new Personnel(['license_status' => 'expired']);
        return $pers->isLicenseExpired() ? 'Detected expired professional engineer license status' : false;
    });

    addTest($testCases, $counter, 'PRC Personnel & Compliance', 'Model Method', 'Personnel isLicenseExpired() detects past license expiration date', 'license_status = "active", expiry = yesterday', 'Should evaluate to true', function() {
        $pers = new Personnel(['license_status' => 'active', 'license_expiry_date' => now()->subDay()]);
        return $pers->isLicenseExpired() ? 'Detected past license expiry date boundary' : false;
    });

    addTest($testCases, $counter, 'PRC Personnel & Compliance', 'Model Method', 'Personnel isLicenseExpired() validates active valid license', 'license_status = "active", expiry = next year', 'Should evaluate to false', function() {
        $pers = new Personnel(['license_status' => 'active', 'license_expiry_date' => now()->addYear()]);
        return !$pers->isLicenseExpired() ? 'Validated active compliant license with future expiry' : false;
    });

    addTest($testCases, $counter, 'PRC Personnel & Compliance', 'Accessor', 'Personnel license_status_badge displays EXPIRED LICENSE badge', 'license_status = "revoked"', 'Should return EXPIRED LICENSE badge with danger red background', function() {
        $pers = new Personnel(['license_status' => 'revoked']);
        return ($pers->license_status_badge['label'] === 'EXPIRED LICENSE' && $pers->license_status_badge['bg'] === '#ef4444') ? 'Rendered EXPIRED LICENSE compliance alert' : false;
    });

    addTest($testCases, $counter, 'PRC Personnel & Compliance', 'Accessor', 'Personnel license_status_badge displays ACTIVE badge', 'license_status = "active", expiry in future', 'Should return ACTIVE badge with emerald background', function() {
        $pers = new Personnel(['license_status' => 'active', 'license_expiry_date' => now()->addYear()]);
        return ($pers->license_status_badge['label'] === 'ACTIVE' && $pers->license_status_badge['bg'] === '#10b981') ? 'Rendered ACTIVE compliant practitioner badge' : false;
    });

    // -------------------------------------------------------------
    // PROJECT COSTING & BUDGETING
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'Project Costing & Budgeting', 'Accessor', 'ProjectCost variance calculates budget difference', 'estimated = 100000, actual = 85000', 'Should return positive variance of 15000.00', function() {
        $pc = new ProjectCost(['estimated_cost' => 100000, 'actual_cost' => 85000]);
        return $pc->variance === 15000.00 ? 'Calculated accurate cost variance of +PHP 15,000.00' : false;
    });

    addTest($testCases, $counter, 'Project Costing & Budgeting', 'Accessor', 'ProjectCost variance_percent handles zero estimated cost boundary', 'estimated = 0, actual = 5000', 'Should return 0.0 to prevent division by zero', function() {
        $pc = new ProjectCost(['estimated_cost' => 0, 'actual_cost' => 5000]);
        return $pc->variance_percent === 0.0 ? 'Guarded against division by zero on empty cost estimates' : false;
    });

    addTest($testCases, $counter, 'Project Costing & Budgeting', 'Accessor', 'ProjectCost variance_percent calculates exact cost saving percentage', 'estimated = 200000, actual = 150000', 'Should return 25.0%', function() {
        $pc = new ProjectCost(['estimated_cost' => 200000, 'actual_cost' => 150000]);
        return $pc->variance_percent === 25.0 ? 'Calculated exact 25.0% under-budget variance ratio' : false;
    });

    // -------------------------------------------------------------
    // SITE BILL OF MATERIALS (BOM) & RETURNS
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'Site BOM & Material Movement', 'Accessor', 'ProjectMaterial remaining_qty calculates unconsumed site stock', 'allocated=100, used=60, excess=20', 'Should return 20 units remaining', function() {
        $pm = new ProjectMaterial(['allocated_qty' => 100, 'used_qty' => 60, 'excess_returned_qty' => 20]);
        return $pm->remaining_qty === 20 ? 'Calculated 20 remaining site units (100 - 60 - 20)' : false;
    });

    addTest($testCases, $counter, 'Site BOM & Material Movement', 'Accessor', 'ProjectMaterial net_allocated_qty deducts returned excess stock', 'allocated=100, excess=25', 'Should return 75 net units', function() {
        $pm = new ProjectMaterial(['allocated_qty' => 100, 'excess_returned_qty' => 25]);
        return $pm->net_allocated_qty === 75 ? 'Deducted returned excess to determine net site allocations' : false;
    });

    addTest($testCases, $counter, 'Site BOM & Material Movement', 'Accessor', 'ProjectMaterial returned_excess_value calculates monetary credit', 'excess=10, unit_price=350.00', 'Should return 3500.00 credit value', function() {
        $pm = new ProjectMaterial(['excess_returned_qty' => 10, 'unit_price' => 350.00]);
        return $pm->returned_excess_value === 3500.00 ? 'Evaluated PHP 3,500.00 returned inventory credit value' : false;
    });

    // -------------------------------------------------------------
    // DETAILED ESTIMATES & SCOPE RECALCULATION
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'Detailed Estimates & Markups', 'Model Method', 'ProjectScopeItem recalculate() computes direct cost and markup percentages', 'Direct=30000, Cont=5%, Tax=12%, Prof=10%', 'Should return 38100.00 total item cost', function() {
        $proj = Project::create([
            'project_code' => 'PRJ-FMT-01-' . uniqid(),
            'title' => 'Format Test Proj',
            'client_name' => 'Format Client',
            'land_area_sqm' => 100,
            'floor_area_sqm' => 100,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);
        $si = ProjectScopeItem::create([
            'project_id' => $proj->id,
            'item_number' => '1.0',
            'item_name' => 'Substructure Works',
            'contingency_percent' => 5,
            'taxes_percent' => 12,
            'profit_percent' => 10,
        ]);
        ProjectScopeLine::create([
            'project_scope_item_id' => $si->id,
            'category' => 'material',
            'description' => 'Ready Mix Concrete',
            'quantity' => 10,
            'unit_price' => 3000,
            'total_cost' => 30000,
        ]);
        $si->recalculate();
        $si->refresh();
        return $si->total_item_cost === 38100.00 ? 'Computed direct costs and 27% total markups (PHP 38,100.00)' : false;
    });

    addTest($testCases, $counter, 'Detailed Estimates & Markups', 'Accessor', 'ProjectScopeLine remaining_quantity clamps negative balance to zero', 'qty=50, used=40, excess=20', 'Should clamp remaining quantity to 0.0', function() {
        $sl = new ProjectScopeLine(['quantity' => 50, 'used_quantity' => 40, 'excess_returned_quantity' => 20]);
        return $sl->remaining_quantity === 0.0 ? 'Guarded against negative remaining quantity balances' : false;
    });

    // -------------------------------------------------------------
    // PROJECT TASKS & SCHEDULING
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'Project Tasks & Scheduling', 'Accessor', 'ProjectTask is_completed evaluates 100% progress to completed', 'progress = 100', 'Should evaluate to true', function() {
        $t = new ProjectTask(['progress' => 100, 'status' => 'not_started']);
        return $t->is_completed ? 'Evaluated completed status from 100% progress metric' : false;
    });

    addTest($testCases, $counter, 'Project Tasks & Scheduling', 'Accessor', 'ProjectTask status_badge_class evaluates in_progress threshold', 'progress = 45', 'Should return "in_progress"', function() {
        $t = new ProjectTask(['progress' => 45, 'status' => 'not_started']);
        return $t->status_badge_class === 'in_progress' ? 'Evaluated in_progress UI badge styling class' : false;
    });

    addTest($testCases, $counter, 'Project Tasks & Scheduling', 'Accessor', 'ProjectTask timeline_phase_key parses MEP Rough-Ins keyword', 'timeline_phase = "Phase 3: MEP Rough-Ins"', 'Should return "phase3"', function() {
        $t = new ProjectTask(['timeline_phase' => 'Phase 3: MEP Rough-Ins']);
        return $t->timeline_phase_key === 'phase3' ? 'Parsed timeline phase key "phase3" from description' : false;
    });

    addTest($testCases, $counter, 'Project Tasks & Scheduling', 'Mutation Hook', 'ProjectTaskMaterial boot saving hook auto-multiplies quantity by unit cost', 'qty = 12.5, unit_cost = 400.00', 'Should calculate total_cost = 5000.00', function() {
        $proj = Project::create([
            'project_code' => 'PRJ-FMT-02-' . uniqid(),
            'title' => 'Format Test Proj 2',
            'client_name' => 'Format Client 2',
            'land_area_sqm' => 100,
            'floor_area_sqm' => 100,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);
        $task = ProjectTask::create([
            'project_id' => $proj->id,
            'task_name' => 'Framing',
            'start_date' => '2026-01-01',
            'due_date' => '2026-01-15',
        ]);
        $tm = ProjectTaskMaterial::create([
            'project_task_id' => $task->id,
            'material_name' => 'Steel Studs',
            'unit_cost' => 400.00,
            'quantity' => 12.5,
        ]);
        return $tm->total_cost === 5000.00 ? 'Boot saving hook calculated exact total cost (PHP 5,000.00)' : false;
    });

    // -------------------------------------------------------------
    // PROJECT ENGINE & FINANCIAL METRICS
    // -------------------------------------------------------------
    addTest($testCases, $counter, 'Project Engine & Governance', 'Accessor', 'Project structural_weight falls back to 40% when 0', 'structural_weight = 0', 'Should return default weight of 40', function() {
        $p = new Project(['structural_weight' => 0]);
        return $p->structural_weight === 40 ? 'Applied default structural trade weighting of 40%' : false;
    });

    addTest($testCases, $counter, 'Project Engine & Governance', 'Model Method', 'Project recalculateTradeProgressFromTasks() calculates weighted overall progress', 'Structural=80% (wt 50), Electrical=60% (wt 50)', 'Should return 70% overall progress', function() {
        $p = Project::create([
            'project_code' => 'PRJ-FMT-03-' . uniqid(),
            'title' => 'Format Test Proj 3',
            'client_name' => 'Format Client 3',
            'land_area_sqm' => 100,
            'floor_area_sqm' => 100,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'structural_weight' => 50,
            'electrical_weight' => 50,
            'status' => 'in_progress',
        ]);
        ProjectTask::create(['project_id' => $p->id, 'task_name' => 'S1', 'category' => 'Structural', 'start_date' => '2026-01-01', 'due_date' => '2026-02-01', 'progress' => 80]);
        ProjectTask::create(['project_id' => $p->id, 'task_name' => 'E1', 'category' => 'Electrical', 'start_date' => '2026-01-01', 'due_date' => '2026-02-01', 'progress' => 60]);
        $p->recalculateTradeProgressFromTasks();
        $p->refresh();
        return $p->overall_progress === 70 ? 'Calculated exact 70% weighted overall milestone progress' : false;
    });

    addTest($testCases, $counter, 'Project Engine & Governance', 'Accessor', 'Project remaining_budget calculates contract minus spent budget', 'contract = 1000000, spent = 400000', 'Should return 600000.00 remaining budget', function() {
        $p = new Project(['contract_budget' => 1000000, 'spent_budget' => 400000]);
        return $p->remaining_budget === 600000.00 ? 'Evaluated remaining contract budget of PHP 600,000.00' : false;
    });

    addTest($testCases, $counter, 'Project Engine & Governance', 'Accessor', 'Project budget_usage_percent calculates accurate disbursement ratio', 'contract = 1000000, spent = 400000', 'Should return 40.0% usage', function() {
        $p = new Project(['contract_budget' => 1000000, 'spent_budget' => 400000]);
        return $p->budget_usage_percent === 40.0 ? 'Calculated 40.0% budget utilization percentage' : false;
    });

    addTest($testCases, $counter, 'Project Engine & Governance', 'Accessor', 'Project total_deployed_manpower sums all 7 trade labor categories', 'workers=12, skilled=8, engineers=2, foremen=1, safety=1', 'Should return 24 total personnel', function() {
        $p = new Project([
            'deployed_workers' => 12,
            'deployed_skilled_workers' => 8,
            'deployed_engineers' => 2,
            'deployed_foremen' => 1,
            'deployed_safety_officers' => 1,
        ]);
        return $p->total_deployed_manpower === 24 ? 'Aggregated 24 total deployed on-site construction workforce' : false;
    });

    addTest($testCases, $counter, 'Project Engine & Governance', 'Accessor', 'Project cost_health_status detects cost budget overrun condition', 'contract_budget = 500000, spent_budget = 550000', 'Should return "overrun" status', function() {
        $p = new Project(['contract_budget' => 500000, 'spent_budget' => 550000]);
        return $p->cost_health_status === 'overrun' ? 'Identified budget overrun condition (>100% budget spent)' : false;
    });

    addTest($testCases, $counter, 'Project Engine & Governance', 'Accessor', 'Project schedule_health_status handles completed project turnover', 'status = "completed"', 'Should return "completed" health status', function() {
        $p = new Project(['status' => 'completed', 'start_date' => '2026-01-01', 'end_date' => '2026-06-01']);
        return $p->schedule_health_status['status'] === 'completed' ? 'Displayed Project Completed & Turned Over status badge' : false;
    });

} finally {
    DB::rollBack();
}

$headers = [
    'Test Case ID',
    'Use Case',
    'Tested Code Segment',
    'Test Description',
    'Input Values',
    'Expected Behavior',
    'Actual Behavior',
    'Result',
    'Duration',
];

// Write to formatted CSV files
$csvPaths = [
    __DIR__ . '/../WHITEBOX_TEST_RESULTS_FORMATTED.csv',
    __DIR__ . '/../WHITEBOX_TEST_RESULTS.csv',
];

foreach ($csvPaths as $path) {
    try {
        $fp = @fopen($path, 'w');
        if ($fp) {
            fputcsv($fp, $headers);
            foreach ($testCases as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);
            echo "Successfully written: " . realpath($path) . "\n";
        }
    } catch (\Throwable $e) {
        echo "Could not write to {$path}: " . $e->getMessage() . "\n";
    }
}

// Also export TSV version for quick copy-pasting
$tsvPath = __DIR__ . '/../WHITEBOX_TEST_RESULTS.tsv';
$tp = @fopen($tsvPath, 'w');
if ($tp) {
    fputcsv($tp, $headers, "\t");
    foreach ($testCases as $row) {
        fputcsv($tp, $row, "\t");
    }
    fclose($tp);
    echo "Successfully written TSV: " . realpath($tsvPath) . "\n";
}

echo sprintf("Formatted %d test cases successfully!\n", count($testCases));
