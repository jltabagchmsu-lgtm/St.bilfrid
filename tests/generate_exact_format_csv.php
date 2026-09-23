<?php

/**
 * Generate White-Box Testing CSV Following User's Exact 9-Column Specification:
 * 1. Test Case ID
 * 2. Use Case
 * 3. Tested Code Segment
 * 4. Test Description
 * 5. Input Values
 * 6. Expected Behavior
 * 7. Actual Behavior
 * 8. Result
 * 9. Duration
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\InventoryLog;
use App\Models\Payment;
use App\Models\Personnel;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectMaterial;
use App\Models\ProjectScopeItem;
use App\Models\ProjectScopeLine;
use App\Models\ProjectTask;
use App\Models\ProjectTaskMaterial;
use App\Models\ServiceRequest;
use App\Models\Material;
use Illuminate\Support\Facades\Validator;

$rawCases = require __DIR__ . '/whitebox_cases.php';

// Mapping definitions to format into user's exact 9 columns
$meta = [
    'TC-B001' => ['use_case' => 'Authentication & Security', 'segment' => 'Model', 'desc' => 'User::isAdmin() should evaluate null role to master admin default (true)'],
    'TC-B002' => ['use_case' => 'Authentication & Security', 'segment' => 'Model', 'desc' => 'User::isAdmin() should evaluate explicit admin role to true'],
    'TC-B003' => ['use_case' => 'Authentication & Security', 'segment' => 'Model', 'desc' => 'User::isAdmin() should evaluate non-admin role to false'],
    'TC-B004' => ['use_case' => 'Authentication & Security', 'segment' => 'Model', 'desc' => 'User::isRoofingOfficer() should identify roofing portal officer'],
    'TC-B005' => ['use_case' => 'Authentication & Security', 'segment' => 'Model', 'desc' => 'User::isRoofingOfficer() should reject non-roofing role'],
    'TC-B006' => ['use_case' => 'Authentication & Security', 'segment' => 'Model', 'desc' => 'User::isWindowsDoorsOfficer() should identify windows & doors officer'],
    'TC-B007' => ['use_case' => 'Authentication & Security', 'segment' => 'Model', 'desc' => 'User::isWindowsDoorsOfficer() should reject non-windows role'],
    'TC-B008' => ['use_case' => 'Authentication & Security', 'segment' => 'Model', 'desc' => 'User::isSupplier() should identify user with supplier role'],
    'TC-B009' => ['use_case' => 'Authentication & Security', 'segment' => 'Model', 'desc' => 'User::isSupplier() should identify user with non-null supplier_id'],
    'TC-B010' => ['use_case' => 'Authentication & Security', 'segment' => 'Model', 'desc' => 'User::isSupplier() should return false when both role and supplier_id are non-supplier'],
    'TC-B011' => ['use_case' => 'User Registration & Profiles', 'segment' => 'Model', 'desc' => 'User::getRoleTitleAttribute should format supplier name and category'],
    'TC-B012' => ['use_case' => 'User Registration & Profiles', 'segment' => 'Model', 'desc' => 'User::getRoleTitleAttribute should fallback to generic Supplier Account if relation is null'],
    'TC-B013' => ['use_case' => 'User Registration & Profiles', 'segment' => 'Model', 'desc' => 'User::getRoleTitleAttribute should return Roofing Transfer Officer title'],
    'TC-B014' => ['use_case' => 'User Registration & Profiles', 'segment' => 'Model', 'desc' => 'User::getRoleTitleAttribute should return Windows & Doors Transfer Officer title'],
    'TC-B015' => ['use_case' => 'User Registration & Profiles', 'segment' => 'Model', 'desc' => 'User::getRoleTitleAttribute should return Master Administrator for admin role'],
    'TC-B016' => ['use_case' => 'Geolocation & Routing', 'segment' => 'Service', 'desc' => 'User::getPortalRouteAttribute should resolve route for supplier dashboard'],
    'TC-B017' => ['use_case' => 'Geolocation & Routing', 'segment' => 'Service', 'desc' => 'User::getPortalRouteAttribute should resolve route for roofing transfer portal'],
    'TC-B018' => ['use_case' => 'Geolocation & Routing', 'segment' => 'Service', 'desc' => 'User::getPortalRouteAttribute should resolve route for windows & doors portal'],
    'TC-B019' => ['use_case' => 'Geolocation & Routing', 'segment' => 'Service', 'desc' => 'User::getPortalRouteAttribute should fallback to system dashboard url'],
    'TC-B020' => ['use_case' => 'Asset & Provider Management', 'segment' => 'Model', 'desc' => 'Supplier::isActive() should evaluate active status to true'],
    'TC-B021' => ['use_case' => 'Asset & Provider Management', 'segment' => 'Model', 'desc' => 'Supplier::isActive() should evaluate inactive status to false'],
    'TC-B022' => ['use_case' => 'Asset & Provider Management', 'segment' => 'Model', 'desc' => 'Supplier::getCategoryColorAttribute should return hex color for Windows & Doors'],
    'TC-B023' => ['use_case' => 'Asset & Provider Management', 'segment' => 'Model', 'desc' => 'Supplier::getCategoryColorAttribute should return hex color for Roofing'],
    'TC-B024' => ['use_case' => 'Asset & Provider Management', 'segment' => 'Model', 'desc' => 'Supplier::getCategoryColorAttribute should return hex color for Structural & Masonry'],
    'TC-B025' => ['use_case' => 'Form Data Validation', 'segment' => 'Component', 'desc' => 'TaskStoreRequest rules() should reject start_date before project_start'],
    'TC-B026' => ['use_case' => 'Asset & Provider Management', 'segment' => 'Model', 'desc' => 'SupplierMaterial::getStatusBadgeAttribute should return Unavailable when inactive'],
    'TC-B027' => ['use_case' => 'Asset & Provider Management', 'segment' => 'Model', 'desc' => 'SupplierMaterial::getStatusBadgeAttribute should return Unavailable when unavailable'],
    'TC-B028' => ['use_case' => 'Asset & Provider Management', 'segment' => 'Model', 'desc' => 'SupplierMaterial::getStatusBadgeAttribute should return Available when active and in stock'],
    'TC-B029' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'SupplierOrder::getStatusBadgeAttribute should format Pending Approval badge'],
    'TC-B030' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'SupplierOrder::getStatusBadgeAttribute should format Confirmed badge'],
    'TC-B031' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'SupplierOrder::getStatusBadgeAttribute should format Processing badge'],
    'TC-B032' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'SupplierOrder::getStatusBadgeAttribute should format Ready for Delivery badge'],
    'TC-B033' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'SupplierOrder::getStatusBadgeAttribute should format Delivered badge'],
    'TC-B034' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'SupplierOrder::getStatusBadgeAttribute should format Completed badge'],
    'TC-B035' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'SupplierOrder::getStatusBadgeAttribute should format Cancelled badge'],
    'TC-B036' => ['use_case' => 'Inventory & Materials Control', 'segment' => 'Model', 'desc' => 'SupplierOrder::syncToInventory() should block duplicate execution when already synced'],
    'TC-B037' => ['use_case' => 'Inventory & Materials Control', 'segment' => 'Service', 'desc' => 'SupplierOrder::syncToInventory() should credit stock and mark synced on delivery'],
    'TC-B038' => ['use_case' => 'Inventory & Materials Control', 'segment' => 'Model', 'desc' => 'InventoryLog::getTransactionBadgeAttribute should format Excess Material Returned badge'],
    'TC-B039' => ['use_case' => 'Inventory & Materials Control', 'segment' => 'Model', 'desc' => 'InventoryLog::getTransactionBadgeAttribute should format Site BOM Allocation badge'],
    'TC-B040' => ['use_case' => 'Inventory & Materials Control', 'segment' => 'Model', 'desc' => 'InventoryLog::getTransactionBadgeAttribute should format Site Consumption Recorded badge'],
    'TC-B041' => ['use_case' => 'Inventory & Materials Control', 'segment' => 'Model', 'desc' => 'InventoryLog::getTransactionBadgeAttribute should format Warehouse Restock badge'],
    'TC-B042' => ['use_case' => 'Inventory & Materials Control', 'segment' => 'Model', 'desc' => 'InventoryLog::getTransactionBadgeAttribute should format Inventory Adjustment badge'],
    'TC-B043' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getReceiptUrlAttribute should return null when receipt_file is empty'],
    'TC-B044' => ['use_case' => 'Form Data Validation', 'segment' => 'Component', 'desc' => 'PaymentReceiptUploadRequest rules() should accept valid mobile JFIF image format'],
    'TC-B045' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getReceiptUrlAttribute should preserve absolute file paths'],
    'TC-B046' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getReceiptUrlAttribute should prepend standard receipt uploads path'],
    'TC-B047' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getEffectiveOrNumberAttribute should return official receipt number when present'],
    'TC-B048' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getEffectiveOrNumberAttribute should generate structured auto OR number'],
    'TC-B049' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getFinancingTypeLabelAttribute should return Bank Construction Loan label'],
    'TC-B050' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getFinancingTypeLabelAttribute should return Pag-IBIG (HDMF) Loan label'],
    'TC-B051' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getFinancingTypeLabelAttribute should return Client Direct Equity label'],
    'TC-B052' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getFinancingTypeLabelAttribute should return Direct Progress Cash label'],
    'TC-B053' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getConstructionClearanceBadgeAttribute should authorize construction for paid status'],
    'TC-B054' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'Payment::getConstructionClearanceBadgeAttribute should flag Inspection Scheduled'],
    'TC-B055' => ['use_case' => 'Personnel & License Compliance', 'segment' => 'Model', 'desc' => 'Personnel::isLicenseExpired() should evaluate expired status flag to true'],
    'TC-B056' => ['use_case' => 'Personnel & License Compliance', 'segment' => 'Model', 'desc' => 'Personnel::isLicenseExpired() should evaluate past expiry date to true (expired)'],
    'TC-B057' => ['use_case' => 'Personnel & License Compliance', 'segment' => 'Model', 'desc' => 'Personnel::isLicenseExpired() should evaluate future expiry date to false (active)'],
    'TC-B058' => ['use_case' => 'Personnel & License Compliance', 'segment' => 'Model', 'desc' => 'Personnel::getLicenseStatusBadgeAttribute should return EXPIRED LICENSE badge'],
    'TC-B059' => ['use_case' => 'Personnel & License Compliance', 'segment' => 'Model', 'desc' => 'Personnel::getLicenseStatusBadgeAttribute should return ACTIVE badge for valid license'],
    'TC-B060' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'ProjectCost::getVarianceAttribute should calculate variance (estimated - actual)'],
    'TC-B061' => ['use_case' => 'Billing & Pricing Engine', 'segment' => 'Model', 'desc' => 'ProjectCost::getVariancePercentAttribute should return 0 for zero estimated cost guard'],
    'TC-B062' => ['use_case' => 'Form Data Validation', 'segment' => 'Component', 'desc' => 'InventoryController::allocate() should reject fractional quantity for discrete units (pcs/sets)'],
    'TC-B063' => ['use_case' => 'Site Materials & Excess Tracking', 'segment' => 'Model', 'desc' => 'ProjectMaterial::getRemainingQtyAttribute should compute remaining unconsumed site quantity'],
    'TC-B064' => ['use_case' => 'Site Materials & Excess Tracking', 'segment' => 'Model', 'desc' => 'ProjectMaterial::getNetAllocatedQtyAttribute should subtract excess returned quantity'],
    'TC-B065' => ['use_case' => 'Site Materials & Excess Tracking', 'segment' => 'Model', 'desc' => 'ProjectMaterial::getReturnedExcessValueAttribute should evaluate excess financial valuation'],
    'TC-B066' => ['use_case' => 'Scope & DUPA Breakdown', 'segment' => 'Model', 'desc' => 'ProjectScopeItem::recalculate() should rollup DUPA direct cost, contingency, tax, and profit'],
    'TC-B067' => ['use_case' => 'Scope & DUPA Breakdown', 'segment' => 'Model', 'desc' => 'ProjectScopeLine::getRemainingQuantityAttribute should clamp negative quantities to zero'],
    'TC-B068' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'ProjectTask::getIsCompletedAttribute should return true when progress reaches 100%'],
    'TC-B069' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'ProjectTask::getStatusBadgeClassAttribute should return in_progress CSS class for active task'],
    'TC-B070' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'ProjectTask::getTimelinePhaseKeyAttribute should normalize phase name to identifier key'],
    'TC-B071' => ['use_case' => 'Booking & Task Management', 'segment' => 'Model', 'desc' => 'ProjectTaskMaterial boot saving hook should automatically compute total_cost'],
    'TC-B072' => ['use_case' => 'Core System Calculations', 'segment' => 'Model', 'desc' => 'Project::getStructuralWeightAttribute should fallback to default 40% trade weight'],
    'TC-B073' => ['use_case' => 'Core System Calculations', 'segment' => 'Service', 'desc' => 'Project::recalculateTradeProgressFromTasks() should compute weighted overall trade progress'],
    'TC-B074' => ['use_case' => 'Core System Calculations', 'segment' => 'Model', 'desc' => 'Project::getRemainingBudgetAttribute should calculate remaining budget (contract - spent)'],
    'TC-B075' => ['use_case' => 'Core System Calculations', 'segment' => 'Model', 'desc' => 'Project::getBudgetUsagePercentAttribute should calculate budget consumption ratio'],
    'TC-B076' => ['use_case' => 'Core System Calculations', 'segment' => 'Model', 'desc' => 'Project::getTotalDeployedManpowerAttribute should aggregate workforce across all trade categories'],
    'TC-B077' => ['use_case' => 'Core System Calculations', 'segment' => 'Model', 'desc' => 'Project::getCostHealthStatusAttribute should flag overrun when spent budget exceeds contract'],
    'TC-B078' => ['use_case' => 'Core System Calculations', 'segment' => 'Model', 'desc' => 'Project::getScheduleHealthStatusAttribute should return completed state dictionary upon finish'],
    'TC-B079' => ['use_case' => 'Form Data Validation', 'segment' => 'Service', 'desc' => 'ProjectMaterialTransferController store() should reject transfer quantity <= 0'],
    'TC-B080' => ['use_case' => 'Core System Calculations', 'segment' => 'Model', 'desc' => 'ServiceRequest should compute estimated project contract value (floor area * cost per sqm)'],
];

$outputRows = [];
$counter = 1;

foreach ($rawCases as $c) {
    $id = $c['id'];
    $m = $meta[$id] ?? [
        'use_case' => 'Core System Components',
        'segment' => 'Model',
        'desc' => $c['class'] . '::' . $c['method'] . ' ' . $c['technique']
    ];

    $formattedId = 'TC-' . str_pad($counter++, 3, '0', STR_PAD_LEFT);

    $start = microtime(true);
    $status = 'Pass';
    $actualObserved = '';

    try {
        $res = ($c['evaluator'])();
        $isPass = $res['raw'] ?? false;
        $val = $res['val'] ?? 'null';

        if ($isPass) {
            $status = 'Pass';
            $actualObserved = is_string($val) ? $val : 'Executed successfully and verified exact internal invariant';
            // Clean up phrasing to match human testing records
            if ($actualObserved === 'true') $actualObserved = 'Evaluated true; condition verified';
            if ($actualObserved === 'false') $actualObserved = 'Evaluated false; condition verified';
        } else {
            $status = 'Fail';
            $actualObserved = 'Condition failed: evaluated ' . (is_string($val) ? $val : json_encode($val));
        }
    } catch (\Throwable $e) {
        $status = 'Fail';
        $actualObserved = 'Exception caught: ' . $e->getMessage();
    }

    $durationMs = max(1, round((microtime(true) - $start) * 1000));

    $inputVal = is_array($c['inputs']) ? json_encode($c['inputs']) : (string)$c['inputs'];
    $expectedVal = is_array($c['expected']) ? json_encode($c['expected']) : (string)$c['expected'];

    $outputRows[] = [
        'Test Case ID' => $formattedId,
        'Use Case' => $m['use_case'],
        'Tested Code Segment' => $m['segment'],
        'Test Description' => $m['desc'],
        'Input Values' => $inputVal,
        'Expected Behavior' => $expectedVal,
        'Actual Behavior' => $actualObserved,
        'Result' => $status,
        'Duration' => $durationMs . ' ms',
    ];
}

// 1. Write WHITEBOX_TEST_CASES_EXECUTION.csv
$csvPath = base_path('WHITEBOX_TEST_CASES_EXECUTION.csv');
$fp = fopen($csvPath, 'w');

fputcsv($fp, [
    'Test Case ID',
    'Use Case',
    'Tested Code Segment',
    'Test Description',
    'Input Values',
    'Expected Behavior',
    'Actual Behavior',
    'Result',
    'Duration'
]);

foreach ($outputRows as $row) {
    fputcsv($fp, [
        $row['Test Case ID'],
        $row['Use Case'],
        $row['Tested Code Segment'],
        $row['Test Description'],
        $row['Input Values'],
        $row['Expected Behavior'],
        $row['Actual Behavior'],
        $row['Result'],
        $row['Duration']
    ]);
}
fclose($fp);

echo "Successfully exported " . count($outputRows) . " test cases to: {$csvPath}\n";

// Also update WHITEBOX_TEST_RESULTS.csv with this format
$altPath = base_path('WHITEBOX_TEST_RESULTS.csv');
copy($csvPath, $altPath);
echo "Successfully mirrored to: {$altPath}\n";
