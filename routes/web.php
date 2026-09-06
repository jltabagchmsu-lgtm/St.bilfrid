<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectHistoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\EstimationController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ProjectCostController;
use App\Http\Controllers\ProjectScopeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoofingTransferController;
use App\Http\Controllers\WindowsDoorsTransferController;
use App\Http\Controllers\SupplierPortalController;
use App\Http\Controllers\AdminSupplierController;

/*
|--------------------------------------------------------------------------
| Web Routes - Construction Firm Management & Monitoring System
|--------------------------------------------------------------------------
*/

// Authentication Routes (Public / Guest)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 1-Click Live Web Migration & Supplier Seeding Trigger
Route::get('/setup-suppliers', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        (new \Database\Seeders\SupplierManagementSeeder())->run();
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return response('<div style="font-family:sans-serif;background:#0b0f17;color:#10b981;padding:40px;min-height:100vh;"><h2>Database Sync Successful!</h2><p style="color:#f8fafc;">All supplier tables migrated and 3 supplier accounts created successfully on live server.</p><p><a href="/login" style="color:#38bdf8;text-decoration:none;font-weight:bold;">&rarr; Go to Login Page</a></p></div>');
    } catch (\Throwable $e) {
        return response('<div style="font-family:sans-serif;background:#0b0f17;color:#ef4444;padding:40px;min-height:100vh;"><h2>Setup Error</h2><pre style="color:#f8fafc;">' . htmlspecialchars($e->getMessage()) . '</pre></div>', 500);
    }
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // =========================================================================
    // 1. Dedicated Roofing Materials Transfer Portal
    // =========================================================================
    Route::prefix('roofing-transfer')->group(function () {
        // Read-only viewing and slip printing (Allowed for Roofing Officer & Admin)
        Route::middleware(['role:roofing_transfer,admin'])->group(function () {
            Route::get('/', [RoofingTransferController::class, 'index'])->name('roofing.index');
            Route::get('/voucher/{id}', [RoofingTransferController::class, 'printTransferVoucher'])->name('roofing.printVoucher');
        });

        // Inventory mutations and transfers (Strictly Roofing Transfer Officer Only)
        Route::middleware(['role:roofing_transfer'])->group(function () {
            Route::post('/dispatch-stock', [RoofingTransferController::class, 'dispatchFromStock'])->name('roofing.dispatchStock');
            Route::post('/inter-project', [RoofingTransferController::class, 'transferInterProject'])->name('roofing.transferInterProject');
            Route::post('/return-excess', [RoofingTransferController::class, 'returnExcessToStock'])->name('roofing.returnExcess');
            Route::post('/restock', [RoofingTransferController::class, 'restockStock'])->name('roofing.restockStock');
        });
    });

    // =========================================================================
    // 2. Dedicated Windows & Doors Transfer Portal
    // =========================================================================
    Route::prefix('windows-doors-transfer')->group(function () {
        // Read-only viewing and slip printing (Allowed for Windows/Doors Officer & Admin)
        Route::middleware(['role:windows_doors_transfer,admin'])->group(function () {
            Route::get('/', [WindowsDoorsTransferController::class, 'index'])->name('windowsDoors.index');
            Route::get('/voucher/{id}', [WindowsDoorsTransferController::class, 'printTransferVoucher'])->name('windowsDoors.printVoucher');
        });

        // Inventory mutations and transfers (Strictly Windows & Doors Transfer Officer Only)
        Route::middleware(['role:windows_doors_transfer'])->group(function () {
            Route::post('/dispatch-stock', [WindowsDoorsTransferController::class, 'dispatchFromStock'])->name('windowsDoors.dispatchStock');
            Route::post('/inter-project', [WindowsDoorsTransferController::class, 'transferInterProject'])->name('windowsDoors.transferInterProject');
            Route::post('/return-excess', [WindowsDoorsTransferController::class, 'returnExcessToStock'])->name('windowsDoors.returnExcess');
            Route::post('/restock', [WindowsDoorsTransferController::class, 'restockStock'])->name('windowsDoors.restockStock');
        });
    });

    // =========================================================================
    // 3. Dedicated Supplier Operations Dashboard & Portals
    // =========================================================================
    Route::prefix('supplier')->middleware(['role:supplier'])->group(function () {
        Route::get('/dashboard', [SupplierPortalController::class, 'dashboard'])->name('supplier.dashboard');
        
        // My Materials & Inventory Management
        Route::get('/materials', [SupplierPortalController::class, 'materials'])->name('supplier.materials');
        Route::post('/materials/store', [SupplierPortalController::class, 'storeMaterial'])->name('supplier.materials.store');
        Route::post('/materials/{id}/update', [SupplierPortalController::class, 'updateMaterial'])->name('supplier.materials.update');
        Route::post('/materials/{id}/delete', [SupplierPortalController::class, 'destroyMaterial'])->name('supplier.materials.destroy');
        Route::post('/materials/{id}/quick-stock', [SupplierPortalController::class, 'quickStockUpdate'])->name('supplier.materials.quickStock');
        
        // Supplier Orders Management & Communication
        Route::get('/orders', [SupplierPortalController::class, 'orders'])->name('supplier.orders');
        Route::get('/orders/{id}', [SupplierPortalController::class, 'showOrder'])->name('supplier.orders.show');
        Route::post('/orders/{id}/status', [SupplierPortalController::class, 'updateOrderStatus'])->name('supplier.orders.updateStatus');
        Route::post('/orders/{id}/messages', [SupplierPortalController::class, 'sendMessage'])->name('supplier.orders.sendMessage');
        Route::get('/orders/{id}/messages', [SupplierPortalController::class, 'getMessages'])->name('supplier.orders.getMessages');
        Route::get('/inquiries', [SupplierPortalController::class, 'inquiries'])->name('supplier.inquiries');
        Route::post('/inquiries/{id}/respond', [SupplierPortalController::class, 'respondInquiry'])->name('supplier.inquiries.respond');

        // Profile & Account Settings
        Route::get('/profile', [SupplierPortalController::class, 'profile'])->name('supplier.profile');
        Route::post('/profile', [SupplierPortalController::class, 'updateProfile'])->name('supplier.profile.update');

        // Notifications
        Route::post('/notifications/mark-read', [SupplierPortalController::class, 'markNotificationsRead'])->name('supplier.notifications.markRead');
    });

    // =========================================================================
    // 4. Master Administrator Full Access Routes (Admin Role Only)
    // =========================================================================
    Route::middleware(['role:admin'])->group(function () {

        // Executive Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Centralized Supplier Hub & Management
        Route::prefix('suppliers')->group(function () {
            Route::get('/', [AdminSupplierController::class, 'index'])->name('admin.suppliers.index');
            Route::get('/materials', [AdminSupplierController::class, 'materials'])->name('admin.suppliers.materials');
            Route::get('/orders', [AdminSupplierController::class, 'orders'])->name('admin.suppliers.orders');
            Route::post('/orders/store', [AdminSupplierController::class, 'storeOrder'])->name('admin.suppliers.orders.store');
            Route::post('/orders/{id}/status', [AdminSupplierController::class, 'updateOrderStatus'])->name('admin.suppliers.orders.updateStatus');
            Route::post('/orders/{id}/receive', [AdminSupplierController::class, 'receiveOrder'])->name('admin.suppliers.orders.receive');
            Route::post('/orders/{id}/messages', [AdminSupplierController::class, 'sendMessage'])->name('admin.suppliers.orders.sendMessage');
            Route::get('/orders/{id}/messages', [AdminSupplierController::class, 'getMessages'])->name('admin.suppliers.orders.getMessages');
            Route::post('/inquiries/store', [AdminSupplierController::class, 'storeInquiry'])->name('admin.suppliers.inquiries.store');
            Route::post('/{id}/update', [AdminSupplierController::class, 'updateSupplier'])->name('admin.suppliers.update');
            Route::post('/{id}/toggle-status', [AdminSupplierController::class, 'toggleSupplierStatus'])->name('admin.suppliers.toggleStatus');
        });

        // Active Project Tracker & Monitoring
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
        Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
        Route::post('/projects/{id}/update-details', [ProjectController::class, 'update'])->name('projects.updateDetails');
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::post('/projects/{id}/delete', [ProjectController::class, 'destroy'])->name('projects.deletePost');
        Route::post('/projects/{id}/update-progress', [ProjectController::class, 'updateProgress'])->name('projects.updateProgress');
        Route::post('/projects/{id}/update-progression-bases', [ProjectController::class, 'updateProgressionBases'])->name('projects.updateProgressionBases');
        Route::post('/projects/{id}/update-schedule', [ProjectController::class, 'updateSchedule'])->name('projects.updateSchedule');
        Route::post('/projects/{id}/update-financing', [ProjectController::class, 'updateFinancing'])->name('projects.updateFinancing');
        Route::post('/projects/{id}/update-manpower', [ProjectController::class, 'updateManpower'])->name('projects.updateManpower');
        Route::post('/projects/{id}/add-task', [ProjectController::class, 'addTask'])->name('projects.addTask');
        Route::post('/projects/tasks/{taskId}/update', [ProjectController::class, 'updateTask'])->name('projects.updateTask');
        Route::post('/projects/tasks/{taskId}/toggle', [ProjectController::class, 'toggleTaskChecklist'])->name('projects.tasks.toggle');
        Route::post('/projects/tasks/{taskId}/status', [ProjectController::class, 'updateTaskStatusAjax'])->name('projects.tasks.updateStatus');
        Route::post('/projects/tasks/{taskId}/quick-progress', [ProjectController::class, 'updateTaskQuickProgress'])->name('projects.tasks.quickProgress');
        Route::post('/projects/tasks/{taskId}/quick-timeline', [ProjectController::class, 'updateTaskQuickTimeline'])->name('projects.tasks.quickTimeline');
        Route::get('/projects/{id}/active-materials', [ProjectController::class, 'getActiveMaterialsJson'])->name('projects.activeMaterials');
        Route::get('/projects/{id}/checklist-state', [ProjectController::class, 'getChecklistStateJson'])->name('projects.checklistState');
        Route::delete('/projects/tasks/{taskId}', [ProjectController::class, 'destroyTask'])->name('projects.tasks.destroy');
        Route::post('/projects/{id}/reset-checklist', [ProjectController::class, 'resetChecklist'])->name('projects.resetChecklist');
        Route::post('/projects/{id}/assign-personnel', [ProjectController::class, 'assignPersonnel'])->name('projects.assignPersonnel');

        // Project Photos & Blueprints Gallery
        Route::post('/projects/{id}/photos', [ProjectController::class, 'uploadPhoto'])->name('projects.photos.upload');
        Route::post('/projects/photos/{photoId}/update', [ProjectController::class, 'updatePhoto'])->name('projects.photos.update');
        Route::delete('/projects/photos/{photoId}', [ProjectController::class, 'deletePhoto'])->name('projects.photos.delete');
        Route::post('/projects/photos/{photoId}/primary', [ProjectController::class, 'setPrimaryPhoto'])->name('projects.photos.primary');

        // Official Project Accomplishment Report (Print View)
        Route::get('/projects/{id}/print-report', [ProjectController::class, 'printReport'])->name('projects.printReport');

        // Itemized Bill of Materials & Cost Estimates (DUPA Engine)
        Route::post('/projects/{id}/scope-items', [ProjectScopeController::class, 'storeScopeItem'])->name('projects.scopeItems.store');
        Route::post('/projects/{id}/scope-items/{itemId}/lines', [ProjectScopeController::class, 'storeScopeLine'])->name('projects.scopeLines.store');
        Route::put('/projects/scope-items/{itemId}', [ProjectScopeController::class, 'updateScopeItem'])->name('projects.scopeItems.update');
        Route::put('/projects/scope-lines/{lineId}', [ProjectScopeController::class, 'updateScopeLine'])->name('projects.scopeLines.update');
        Route::delete('/projects/scope-items/{itemId}', [ProjectScopeController::class, 'destroyScopeItem'])->name('projects.scopeItems.destroy');
        Route::delete('/projects/scope-lines/{lineId}', [ProjectScopeController::class, 'destroyScopeLine'])->name('projects.scopeLines.destroy');
        Route::post('/projects/{id}/load-project-template', [ProjectScopeController::class, 'loadProjectTemplate'])->name('projects.loadProjectTemplate');
        Route::post('/projects/{id}/load-bungalow-template', [ProjectScopeController::class, 'loadBungalowTemplate'])->name('projects.loadBungalowTemplate');
        Route::post('/projects/{id}/load-3br-bungalow-template', [ProjectScopeController::class, 'load3BrBungalowTemplate'])->name('projects.load3BrBungalowTemplate');
        Route::post('/projects/{id}/load-2br-bungalow-template', [ProjectScopeController::class, 'load2BrBungalowTemplate'])->name('projects.load2BrBungalowTemplate');
        Route::post('/projects/{id}/load-duplex-template', [ProjectScopeController::class, 'loadDuplexHousingTemplate'])->name('projects.loadDuplexTemplate');
        Route::get('/projects/{id}/print-bom', [ProjectScopeController::class, 'printBom'])->name('projects.printBom');

        // Project Costing & Expenditure Control for Individual Projects
        Route::get('/costing', [ProjectCostController::class, 'index'])->name('costing.index');
        Route::post('/projects/{id}/costs', [ProjectCostController::class, 'store'])->name('projects.costs.store');
        Route::post('/projects/costs/{costId}/update', [ProjectCostController::class, 'update'])->name('projects.costs.update');
        Route::delete('/projects/costs/{costId}', [ProjectCostController::class, 'destroy'])->name('projects.costs.destroy');
        Route::post('/projects/{id}/costs/auto-sync', [ProjectCostController::class, 'autoSync'])->name('projects.costs.autoSync');

        // Separated Materials Inventory (INV)
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
        Route::post('/inventory/{id}/update-stock', [InventoryController::class, 'updateStock'])->name('inventory.updateStock');

        // Separated Bill of Materials (BOM) per Project
        Route::get('/bom', [BomController::class, 'index'])->name('bom.index');
        Route::get('/bom/project/{id}', [BomController::class, 'projectBom'])->name('bom.project');
        Route::post('/bom/project/{id}/auto-allocate-scope', [BomController::class, 'autoAllocateFromScope'])->name('bom.autoAllocateScope');
        Route::post('/bom/store', [BomController::class, 'store'])->name('bom.store');
        Route::post('/bom/{id}/update-usage', [BomController::class, 'updateUsage'])->name('bom.updateUsage');
        Route::post('/bom/{id}/daily-usage', [BomController::class, 'recordDailyUsage'])->name('bom.recordDailyUsage');
        Route::post('/bom/{id}/transfer-project', [BomController::class, 'transferToProject'])->name('bom.transferToProject');
        Route::post('/bom/{id}/return-excess', [BomController::class, 'returnExcessMaterial'])->name('bom.returnExcess');

        // Separated Project History
        Route::get('/history', [ProjectHistoryController::class, 'index'])->name('history.index');

        // Internal Financial Payments & Billing Ledger
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments/store', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('/payments/{id}/update-status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');
        Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::get('/payments/{id}/receipt', [PaymentController::class, 'printReceipt'])->name('payments.printReceipt');

        // Service Cost Estimator & Project Estimations
        Route::get('/estimation', [EstimationController::class, 'index'])->name('estimation.index');
        Route::post('/estimation/store', [EstimationController::class, 'store'])->name('estimation.store');
        Route::post('/estimation/{id}/initialize', [EstimationController::class, 'initializeProject'])->name('estimation.initialize');
        Route::delete('/estimation/{id}', [EstimationController::class, 'destroy'])->name('estimation.destroy');

        // Engineers & Architects Roster
        Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel.index');
        Route::post('/personnel/store', [PersonnelController::class, 'store'])->name('personnel.store');

    });

});
