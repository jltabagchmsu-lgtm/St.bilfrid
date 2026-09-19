# ST. BILFRID DEVELOPMENT CORPORATION — ALPHA TEST EXECUTION REGISTER
## Internal Alpha Test Suite with Defect Register | System Architecture & Eloquent Domain Models

| Metric | Details |
| :--- | :--- |
| **System Name** | St. Bilfrid Construction Management Information System (`NewConstuc.FIRM`) |
| **Test Phase** | **Alpha Testing Phase (Internal QA & Defect Discovery)** |
| **Total Test Cases** | **80 Executed Test Cases (`TC-A001` to `TC-A080`)** |
| **Passed** | **76 Passed (95.0%)** |
| **Failed** | **4 Failed (5.0%)** *(Tracked in Defect Register for Beta Fix)* |
| **Execution Engine** | Playwright Test Runner (@playwright/test) / Chromium v1243 / Node v24 |
| **Export Formats** | [Markdown Dossier](file:///c:/Users/Carin%20Benjamin/Desktop/NewConstuc.FIRM/PLAYWRIGHT_ALL_80_ALPHA_TEST_EVIDENCES_WITH_SCREENSHOTS.md) \| [CSV Spreadsheet](file:///c:/Users/Carin%20Benjamin/Desktop/NewConstuc.FIRM/ALPHA_TEST_EXECUTION_EVIDENCES.csv) \| [Interactive HTML Report](file:///c:/Users/Carin%20Benjamin/Desktop/NewConstuc.FIRM/playwright_all_80_alpha_screenshots_report.html) |

---

## 1. Alpha Defect Register (4 Tracked Defects)

The following 4 defect findings were discovered during the Alpha test execution and scheduled for remediation prior to the Beta release:

| Defect ID | Test Case | Subsystem / Module | Defect Summary | Severity | Priority | Remediation Action |
| :---: | :---: | :--- | :--- | :---: | :---: | :--- |
| **DEFECT-01** | `TC-A025` | Project Management | Hierarchical Date Inversion: Task start date allowed before parent project start date | Medium | High | Add `after_or_equal:project_start_date` validation rule |
| **DEFECT-02** | `TC-A044` | Billing & Receipts | Strict MIME Whitelist: Camera `.jfif` upload rejected with HTTP 422 | Low | Medium | Expand whitelist to `jfif,webp,jpg,png,pdf` |
| **DEFECT-03** | `TC-A062` | Inventory & Materials | Fractional Unit Allocation: Discrete unit (`pcs`) accepted decimal `15.75` and truncated | Medium | High | Add conditional integer check for discrete units |
| **DEFECT-04** | `TC-A079` | Trade Transfers | Zero-Quantity Transfer: System processed `0.00` qty and generated phantom voucher | Low | High | Enforce `gt:0` rule in transfer controller |

---

## 2. Complete 80-Test Execution Matrix

| Test ID | Use Case | Component | Description | Result | Duration |
| :---: | :--- | :--- | :--- | :---: | :---: |
| **TC-A001** | Authentication & Security | `App\Models\User::isAdmin()` | User - isAdmin() returns true for null role (Default Admin in legacy schema) | ✅ **PASS** | 12 ms |
| **TC-A002** | Authentication & Security | `App\Models\User::isAdmin()` | User - isAdmin() returns true for explicit admin role | ✅ **PASS** | 1 ms |
| **TC-A003** | Authentication & Security | `App\Models\User::isAdmin()` | User - isAdmin() returns false for specialized roofing officer role | ✅ **PASS** | 1 ms |
| **TC-A004** | Authentication & Security | `App\Models\User::isRoofingOfficer()` | User - isRoofingOfficer() returns true for roofing_transfer role | ✅ **PASS** | 1 ms |
| **TC-A005** | Authentication & Security | `App\Models\User::isRoofingOfficer()` | User - isRoofingOfficer() returns false for admin role | ✅ **PASS** | 1 ms |
| **TC-A006** | Authentication & Security | `App\Models\User::isWindowsDoorsOfficer()` | User - isWindowsDoorsOfficer() returns true for windows_doors_transfer | ✅ **PASS** | 1 ms |
| **TC-A007** | Authentication & Security | `App\Models\User::isWindowsDoorsOfficer()` | User - isWindowsDoorsOfficer() returns false for roofing_transfer | ✅ **PASS** | 1 ms |
| **TC-A008** | Authentication & Security | `App\Models\User::isSupplier()` | User - isSupplier() returns true for supplier role | ✅ **PASS** | 1 ms |
| **TC-A009** | Authentication & Security | `App\Models\User::isSupplier()` | User - isSupplier() returns true when supplier_id is set (FK Link) | ✅ **PASS** | 1 ms |
| **TC-A010** | Authentication & Security | `App\Models\User::isSupplier()` | User - isSupplier() returns false for regular admin | ✅ **PASS** | 1 ms |
| **TC-A011** | Authentication & Security | `App\Models\User::getRoleTitleAttribute` | User - getRoleTitleAttribute with Supplier Model relation | ✅ **PASS** | 2 ms |
| **TC-A012** | Authentication & Security | `App\Models\User::getRoleTitleAttribute` | User - getRoleTitleAttribute with null Supplier relation fallback | ✅ **PASS** | 7 ms |
| **TC-A013** | Authentication & Security | `App\Models\User::getRoleTitleAttribute` | User - getRoleTitleAttribute for Roofing Officer | ✅ **PASS** | 1 ms |
| **TC-A014** | Authentication & Security | `App\Models\User::getRoleTitleAttribute` | User - getRoleTitleAttribute for Windows Officer | ✅ **PASS** | 1 ms |
| **TC-A015** | Authentication & Security | `App\Models\User::getRoleTitleAttribute` | User - getRoleTitleAttribute default Master Admin | ✅ **PASS** | 1 ms |
| **TC-A016** | Authentication & Security | `App\Models\User::getPortalRouteAttribute` | User - getPortalRouteAttribute for Supplier redirect | ✅ **PASS** | 1 ms |
| **TC-A017** | Authentication & Security | `App\Models\User::getPortalRouteAttribute` | User - getPortalRouteAttribute for Roofing Officer redirect | ✅ **PASS** | 1 ms |
| **TC-A018** | Authentication & Security | `App\Models\User::getPortalRouteAttribute` | User - getPortalRouteAttribute for Windows Officer redirect | ✅ **PASS** | 1 ms |
| **TC-A019** | Authentication & Security | `App\Models\User::getPortalRouteAttribute` | User - getPortalRouteAttribute for Admin root redirect | ✅ **PASS** | 1 ms |
| **TC-A020** | Supply Chain & Supplier Operations | `App\Models\Supplier::isActive()` | Supplier - isActive() returns true for active status | ✅ **PASS** | 1 ms |
| **TC-A021** | Supply Chain & Supplier Operations | `App\Models\Supplier::isActive()` | Supplier - isActive() returns false for inactive status | ✅ **PASS** | 1 ms |
| **TC-A022** | Supply Chain & Supplier Operations | `App\Models\Supplier::getCategoryColorAttribute` | Supplier - getCategoryColorAttribute for Windows & Doors | ✅ **PASS** | 1 ms |
| **TC-A023** | Supply Chain & Supplier Operations | `App\Models\Supplier::getCategoryColorAttribute` | Supplier - getCategoryColorAttribute for Roofing | ✅ **PASS** | 1 ms |
| **TC-A024** | Supply Chain & Supplier Operations | `App\Models\Supplier::getCategoryColorAttribute` | Supplier - getCategoryColorAttribute for Structural | ✅ **PASS** | 1 ms |
| **TC-A025** | Project Management & Progress | `App\Http\Requests\TaskStoreRequest [DEFECT #01]` | ProjectTask - Hierarchical Date Validation (Task Start Date vs Project Start Date) | ❌ **FAIL (DEFECT-01)** | 18 ms |
| **TC-A026** | Supply Chain & Supplier Operations | `App\Models\SupplierMaterial::getStatusBadgeAttribute` | SupplierMaterial - getStatusBadgeAttribute for inactive item | ✅ **PASS** | 2 ms |
| **TC-A027** | Supply Chain & Supplier Operations | `App\Models\SupplierMaterial::getStatusBadgeAttribute` | SupplierMaterial - getStatusBadgeAttribute for out of stock | ✅ **PASS** | 1 ms |
| **TC-A028** | Supply Chain & Supplier Operations | `App\Models\SupplierMaterial::getStatusBadgeAttribute` | SupplierMaterial - getStatusBadgeAttribute for active available item | ✅ **PASS** | 1 ms |
| **TC-A029** | Supply Chain & Supplier Operations | `App\Models\SupplierOrder::getStatusBadgeAttribute` | SupplierOrder - getStatusBadgeAttribute pending status | ✅ **PASS** | 2 ms |
| **TC-A030** | Supply Chain & Supplier Operations | `App\Models\SupplierOrder::getStatusBadgeAttribute` | SupplierOrder - getStatusBadgeAttribute confirmed status | ✅ **PASS** | 1 ms |
| **TC-A031** | Supply Chain & Supplier Operations | `App\Models\SupplierOrder::getStatusBadgeAttribute` | SupplierOrder - getStatusBadgeAttribute processing status | ✅ **PASS** | 1 ms |
| **TC-A032** | Supply Chain & Supplier Operations | `App\Models\SupplierOrder::getStatusBadgeAttribute` | SupplierOrder - getStatusBadgeAttribute ready_for_delivery | ✅ **PASS** | 1 ms |
| **TC-A033** | Supply Chain & Supplier Operations | `App\Models\SupplierOrder::getStatusBadgeAttribute` | SupplierOrder - getStatusBadgeAttribute delivered status | ✅ **PASS** | 1 ms |
| **TC-A034** | Supply Chain & Supplier Operations | `App\Models\SupplierOrder::getStatusBadgeAttribute` | SupplierOrder - getStatusBadgeAttribute completed status | ✅ **PASS** | 1 ms |
| **TC-A035** | Supply Chain & Supplier Operations | `App\Models\SupplierOrder::getStatusBadgeAttribute` | SupplierOrder - getStatusBadgeAttribute cancelled status | ✅ **PASS** | 1 ms |
| **TC-A036** | Supply Chain & Supplier Operations | `App\Models\SupplierOrder::syncToInventory()` | SupplierOrder - syncToInventory() idempotency guard preventing duplicate stock credit | ✅ **PASS** | 1 ms |
| **TC-A037** | Supply Chain & Supplier Operations | `App\Models\SupplierOrder::syncToInventory()` | SupplierOrder - syncToInventory() stock crediting & inventory transaction log creation | ✅ **PASS** | 46 ms |
| **TC-A038** | Inventory & Materials Management | `App\Models\InventoryLog::getTransactionBadgeAttribute` | InventoryLog - getTransactionBadge for excess_return transaction | ✅ **PASS** | 1 ms |
| **TC-A039** | Inventory & Materials Management | `App\Models\InventoryLog::getTransactionBadgeAttribute` | InventoryLog - getTransactionBadge for site allocation transaction | ✅ **PASS** | 1 ms |
| **TC-A040** | Inventory & Materials Management | `App\Models\InventoryLog::getTransactionBadgeAttribute` | InventoryLog - getTransactionBadge for daily usage transaction | ✅ **PASS** | 1 ms |
| **TC-A041** | Inventory & Materials Management | `App\Models\InventoryLog::getTransactionBadgeAttribute` | InventoryLog - getTransactionBadge for restock transaction | ✅ **PASS** | 1 ms |
| **TC-A042** | Inventory & Materials Management | `App\Models\InventoryLog::getTransactionBadgeAttribute` | InventoryLog - getTransactionBadge for physical adjustment | ✅ **PASS** | 1 ms |
| **TC-A043** | Billing & Financial Clearance | `App\Models\Payment::getReceiptUrlAttribute` | Payment - getReceiptUrlAttribute for null file record | ✅ **PASS** | 2 ms |
| **TC-A044** | Billing & Financial Clearance | `App\Http\Requests\PaymentReceiptUploadRequest [DEFECT #02]` | Payment - Receipt File MIME Type Validation for Mobile Camera Uploads (JPEG JFIF) | ❌ **FAIL (DEFECT-02)** | 24 ms |
| **TC-A045** | Billing & Financial Clearance | `App\Models\Payment::getReceiptUrlAttribute` | Payment - getReceiptUrlAttribute for absolute root slash path | ✅ **PASS** | 1 ms |
| **TC-A046** | Billing & Financial Clearance | `App\Models\Payment::getReceiptUrlAttribute` | Payment - getReceiptUrlAttribute for relative filename | ✅ **PASS** | 1 ms |
| **TC-A047** | Billing & Financial Clearance | `App\Models\Payment::getEffectiveOrNumberAttribute` | Payment - getEffectiveOrNumberAttribute for explicit OR number | ✅ **PASS** | 1 ms |
| **TC-A048** | Billing & Financial Clearance | `App\Models\Payment::getEffectiveOrNumberAttribute` | Payment - getEffectiveOrNumberAttribute synthetic fallback pattern | ✅ **PASS** | 1 ms |
| **TC-A049** | Billing & Financial Clearance | `App\Models\Payment::getFinancingTypeLabelAttribute` | Payment - getFinancingTypeLabel for bank_loan | ✅ **PASS** | 1 ms |
| **TC-A050** | Billing & Financial Clearance | `App\Models\Payment::getFinancingTypeLabelAttribute` | Payment - getFinancingTypeLabel for pagibig_loan | ✅ **PASS** | 1 ms |
| **TC-A051** | Billing & Financial Clearance | `App\Models\Payment::getFinancingTypeLabelAttribute` | Payment - getFinancingTypeLabel for client_equity | ✅ **PASS** | 1 ms |
| **TC-A052** | Billing & Financial Clearance | `App\Models\Payment::getFinancingTypeLabelAttribute` | Payment - getFinancingTypeLabel for cash_progress | ✅ **PASS** | 1 ms |
| **TC-A053** | Billing & Financial Clearance | `App\Models\Payment::getConstructionClearanceBadgeAttribute` | Payment - getConstructionClearanceBadge status paid | ✅ **PASS** | 1 ms |
| **TC-A054** | Billing & Financial Clearance | `App\Models\Payment::getConstructionClearanceBadgeAttribute` | Payment - getConstructionClearanceBadge inspection scheduled | ✅ **PASS** | 1 ms |
| **TC-A055** | Personnel & Licensure Compliance | `App\Models\Personnel::isLicenseExpired()` | Personnel - isLicenseExpired() with explicit status expired | ✅ **PASS** | 1 ms |
| **TC-A056** | Personnel & Licensure Compliance | `App\Models\Personnel::isLicenseExpired()` | Personnel - isLicenseExpired() with past expiry date | ✅ **PASS** | 1 ms |
| **TC-A057** | Personnel & Licensure Compliance | `App\Models\Personnel::isLicenseExpired()` | Personnel - isLicenseExpired() with future expiry date | ✅ **PASS** | 1 ms |
| **TC-A058** | Personnel & Licensure Compliance | `App\Models\Personnel::getLicenseStatusBadgeAttribute` | Personnel - getLicenseStatusBadge for expired license | ✅ **PASS** | 1 ms |
| **TC-A059** | Personnel & Licensure Compliance | `App\Models\Personnel::getLicenseStatusBadgeAttribute` | Personnel - getLicenseStatusBadge for active license | ✅ **PASS** | 1 ms |
| **TC-A060** | Cost Engineering & Budget Control | `App\Models\ProjectCost::getVarianceAttribute` | ProjectCost - getVarianceAttribute standard savings computation | ✅ **PASS** | 2 ms |
| **TC-A061** | Cost Engineering & Budget Control | `App\Models\ProjectCost::getVariancePercentAttribute` | ProjectCost - getVariancePercent zero-division safety guard | ✅ **PASS** | 1 ms |
| **TC-A062** | Inventory & Materials Management | `App\Http\Controllers\InventoryController [DEFECT #03]` | InventoryController - Integer Quantity Enforcement for Discrete Material Units | ❌ **FAIL (DEFECT-03)** | 14 ms |
| **TC-A063** | Scope of Works & DUPA Estimation | `App\Models\ProjectMaterial::getRemainingQtyAttribute` | ProjectMaterial - getRemainingQtyAttribute clamp calculation | ✅ **PASS** | 2 ms |
| **TC-A064** | Scope of Works & DUPA Estimation | `App\Models\ProjectMaterial::getNetAllocatedQtyAttribute` | ProjectMaterial - getNetAllocatedQtyAttribute calculation | ✅ **PASS** | 1 ms |
| **TC-A065** | Scope of Works & DUPA Estimation | `App\Models\ProjectMaterial::getReturnedExcessValueAttribute` | ProjectMaterial - getReturnedExcessValueAttribute financial credit | ✅ **PASS** | 1 ms |
| **TC-A066** | Scope of Works & DUPA Estimation | `App\Models\ProjectScopeItem::recalculate()` | ProjectScopeItem - recalculate() DUPA Rollup (Direct + Markups) | ✅ **PASS** | 7 ms |
| **TC-A067** | Scope of Works & DUPA Estimation | `App\Models\ProjectScopeLine::getRemainingQuantityAttribute` | ProjectScopeLine - getRemainingQuantityAttribute zero-floor clamp | ✅ **PASS** | 1 ms |
| **TC-A068** | Project Management & Progress Engine | `App\Models\ProjectTask::getIsCompletedAttribute` | ProjectTask - getIsCompletedAttribute for 100% progress | ✅ **PASS** | 2 ms |
| **TC-A069** | Project Management & Progress Engine | `App\Models\ProjectTask::getStatusBadgeClassAttribute` | ProjectTask - getStatusBadgeClassAttribute for 30% progress | ✅ **PASS** | 1 ms |
| **TC-A070** | Project Management & Progress Engine | `App\Models\ProjectTask::getTimelinePhaseKeyAttribute` | ProjectTask - getTimelinePhaseKey for Superstructure | ✅ **PASS** | 1 ms |
| **TC-A071** | Project Management & Progress Engine | `App\Models\ProjectTaskMaterial::boot()` | ProjectTaskMaterial - boot() saving event auto-calculates total_cost | ✅ **PASS** | 4 ms |
| **TC-A072** | Project Management & Progress Engine | `App\Models\Project::getStructuralWeightAttribute` | Project - getStructuralWeightAttribute fallback to default 40% | ✅ **PASS** | 1 ms |
| **TC-A073** | Project Management & Progress Engine | `App\Models\Project::recalculateTradeProgressFromTasks()` | Project - recalculateTradeProgressFromTasks() Weighted Multi-Trade Aggregation | ✅ **PASS** | 5 ms |
| **TC-A074** | Project Management & Progress Engine | `App\Models\Project::getRemainingBudgetAttribute` | Project - getRemainingBudgetAttribute calculation | ✅ **PASS** | 1 ms |
| **TC-A075** | Project Management & Progress Engine | `App\Models\Project::getBudgetUsagePercentAttribute` | Project - getBudgetUsagePercentAttribute calculation | ✅ **PASS** | 1 ms |
| **TC-A076** | Project Management & Progress Engine | `App\Models\Project::getTotalDeployedManpowerAttribute` | Project - getTotalDeployedManpowerAttribute summation across trades | ✅ **PASS** | 1 ms |
| **TC-A077** | Project Management & Progress Engine | `App\Models\Project::getCostHealthStatusAttribute` | Project - getCostHealthStatusAttribute budget overrun check | ✅ **PASS** | 1 ms |
| **TC-A078** | Project Management & Progress Engine | `App\Models\Project::getScheduleHealthStatusAttribute` | Project - getScheduleHealthStatusAttribute completed status | ✅ **PASS** | 1 ms |
| **TC-A079** | Trade Transfers & Specialized Logistics | `App\Http\Controllers\ProjectMaterialTransferController [DEFECT #04]` | ProjectMaterialTransfer - Inter-Site Transfer with 0.00 Quantity Guard | ❌ **FAIL (DEFECT-04)** | 11 ms |
| **TC-A080** | Pre-Construction Estimator | `App\Models\ServiceRequest::class` | ServiceRequest - Pre-Construction Rough Estimate Calculation | ✅ **PASS** | 3 ms |

---
