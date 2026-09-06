<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierOrderLog;
use App\Models\SupplierNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SupplierPortalController extends Controller
{
    /**
     * Helper to get current authenticated supplier.
     */
    protected function getSupplier(): Supplier
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        if (!$supplier) {
            // Fallback for demo or edge cases: find first or fail
            abort(403, 'Your account is not linked to an authorized supplier organization.');
        }

        return $supplier;
    }

    /**
     * Supplier Dashboard Overview.
     */
    public function dashboard()
    {
        $supplier = $this->getSupplier();

        // 1. Materials Statistics
        $totalMaterials = $supplier->materials()->count();
        $availableMaterials = $supplier->materials()->where('availability_status', 'available')->where('is_active', true)->count();
        $lowStockMaterials = $supplier->materials()->where('availability_status', 'low_stock')->count();
        $outOfStockMaterials = $supplier->materials()->where('availability_status', 'out_of_stock')->count();

        // 2. Orders Statistics
        $pendingOrders = $supplier->orders()->where('status', 'pending')->count();
        $activeOrders = $supplier->orders()->whereIn('status', ['confirmed', 'processing', 'ready_for_delivery'])->count();
        $completedOrders = $supplier->orders()->where('status', 'completed')->count();
        $totalRevenue = $supplier->orders()->whereIn('status', ['delivered', 'completed'])->sum('total_amount');

        // 3. Recent Feeds
        $recentOrders = $supplier->orders()->with(['items', 'orderedBy', 'project'])->latest()->take(6)->get();
        $lowStockAlerts = $supplier->materials()->whereIn('availability_status', ['low_stock', 'out_of_stock'])->orderBy('available_quantity', 'asc')->take(5)->get();
        $recentNotifications = $supplier->notifications()->latest()->take(5)->get();

        return view('supplier.dashboard', compact(
            'supplier',
            'totalMaterials',
            'availableMaterials',
            'lowStockMaterials',
            'outOfStockMaterials',
            'pendingOrders',
            'activeOrders',
            'completedOrders',
            'totalRevenue',
            'recentOrders',
            'lowStockAlerts',
            'recentNotifications'
        ));
    }

    /**
     * My Materials Catalog Management.
     */
    public function materials(Request $request)
    {
        $supplier = $this->getSupplier();

        $query = $supplier->materials();

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('material_code', 'like', "%{$search}%")
                  ->orWhere('subcategory', 'like', "%{$search}%")
                  ->orWhere('specifications', 'like', "%{$search}%");
            });
        }

        // Subcategory filter
        if ($request->filled('subcategory') && $request->subcategory !== 'all') {
            $query->where('subcategory', $request->subcategory);
        }

        // Availability status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('availability_status', $request->status);
        }

        $materials = $query->orderBy('name', 'asc')->paginate(12)->withQueryString();

        // Get unique subcategories for filter pills
        $subcategories = $supplier->materials()->whereNotNull('subcategory')->distinct()->pluck('subcategory');

        return view('supplier.materials', compact('supplier', 'materials', 'subcategories'));
    }

    /**
     * Store a new supplier material.
     */
    public function storeMaterial(Request $request)
    {
        $supplier = $this->getSupplier();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'specifications' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'available_quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'min_order_qty' => 'nullable|integer|min:1',
            'availability_status' => 'nullable|in:available,low_stock,out_of_stock,unavailable',
            'image_url' => 'nullable|string|max:500',
        ]);

        // Generate Material Code if not provided
        $prefix = match ($supplier->category) {
            'Windows & Doors' => 'MAT-WNDR',
            'Roofing' => 'MAT-ROOF',
            'Structural & Masonry' => 'MAT-STRC',
            default => 'MAT-SUP',
        };

        $count = $supplier->materials()->count() + 1;
        $materialCode = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(3));

        // Compute availability status if not explicitly set
        $status = $validated['availability_status'] ?? null;
        if (!$status) {
            if ($validated['available_quantity'] <= 0) {
                $status = 'out_of_stock';
            } elseif ($validated['available_quantity'] <= 10) {
                $status = 'low_stock';
            } else {
                $status = 'available';
            }
        }

        $material = SupplierMaterial::create([
            'supplier_id' => $supplier->id,
            'material_code' => $materialCode,
            'name' => $validated['name'],
            'category' => $supplier->category,
            'subcategory' => $validated['subcategory'] ?? 'General Supplies',
            'description' => $validated['description'] ?? null,
            'specifications' => $validated['specifications'] ?? null,
            'unit' => $validated['unit'],
            'available_quantity' => $validated['available_quantity'],
            'unit_price' => $validated['unit_price'],
            'min_order_qty' => $validated['min_order_qty'] ?? 1,
            'availability_status' => $status,
            'image_url' => $validated['image_url'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('supplier.materials')
            ->with('success', 'Material "' . $material->name . '" has been cataloged successfully under ' . $material->material_code . '.');
    }

    /**
     * Update an existing material.
     */
    public function updateMaterial(Request $request, $id)
    {
        $supplier = $this->getSupplier();
        $material = $supplier->materials()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'specifications' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'available_quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'min_order_qty' => 'nullable|integer|min:1',
            'availability_status' => 'required|in:available,low_stock,out_of_stock,unavailable',
            'image_url' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $material->update([
            'name' => $validated['name'],
            'subcategory' => $validated['subcategory'] ?? $material->subcategory,
            'description' => $validated['description'] ?? null,
            'specifications' => $validated['specifications'] ?? null,
            'unit' => $validated['unit'],
            'available_quantity' => $validated['available_quantity'],
            'unit_price' => $validated['unit_price'],
            'min_order_qty' => $validated['min_order_qty'] ?? 1,
            'availability_status' => $validated['availability_status'],
            'image_url' => $validated['image_url'] ?? $material->image_url,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->back()
            ->with('success', 'Material "' . $material->name . '" specifications and stock have been updated.');
    }

    /**
     * Toggle active state or delete material.
     */
    public function destroyMaterial($id)
    {
        $supplier = $this->getSupplier();
        $material = $supplier->materials()->findOrFail($id);

        // Check if material has active order items
        if ($material->orderItems()->exists()) {
            $material->update(['is_active' => false, 'availability_status' => 'unavailable']);
            return redirect()->back()->with('success', 'Material "' . $material->name . '" is linked to past orders and has been set to Inactive/Unavailable.');
        }

        $name = $material->name;
        $material->delete();

        return redirect()->back()->with('success', 'Material "' . $name . '" has been removed from your catalog.');
    }

    /**
     * Quick stock quantity adjuster.
     */
    public function quickStockUpdate(Request $request, $id)
    {
        $supplier = $this->getSupplier();
        $material = $supplier->materials()->findOrFail($id);

        $validated = $request->validate([
            'available_quantity' => 'required|integer|min:0',
        ]);

        $qty = $validated['available_quantity'];
        $material->available_quantity = $qty;
        $material->syncAvailabilityStatus();
        $material->save();

        return redirect()->back()->with('success', 'Stock quantity for "' . $material->name . '" updated to ' . $qty . ' ' . $material->unit . '.');
    }

    /**
     * View incoming and past purchase orders.
     */
    public function orders(Request $request)
    {
        $supplier = $this->getSupplier();

        $query = $supplier->orders()->with(['items', 'orderedBy', 'project', 'logs']);

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search order code
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhere('delivery_location', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('supplier.orders', compact('supplier', 'orders'));
    }

    /**
     * Show single order details JSON (for modal) or view.
     */
    public function showOrder($id)
    {
        $supplier = $this->getSupplier();
        $order = $supplier->orders()->with(['items', 'orderedBy', 'project', 'logs.user'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($order);
        }

        return view('supplier.order_details', compact('supplier', 'order'));
    }

    /**
     * Update order status workflow.
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $supplier = $this->getSupplier();
        $order = $supplier->orders()->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,ready_for_delivery,delivered,completed,cancelled',
            'comment' => 'nullable|string|max:500',
            'actual_delivery_date' => 'nullable|date',
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        $order->status = $newStatus;
        if ($newStatus === 'delivered' || $newStatus === 'completed') {
            $order->actual_delivery_date = $validated['actual_delivery_date'] ?? now();
        }
        $order->save();

        // Record Audit Log
        SupplierOrderLog::create([
            'supplier_order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'comment' => $validated['comment'] ?? ('Status changed by supplier to ' . ucfirst(str_replace('_', ' ', $newStatus))),
        ]);

        // Notify Admins
        SupplierNotification::create([
            'supplier_id' => $supplier->id,
            'user_id' => null, // Broadcast to admin
            'type' => 'order_status_updated',
            'title' => 'Order ' . $order->order_code . ' Status: ' . ucfirst(str_replace('_', ' ', $newStatus)),
            'message' => $supplier->name . ' updated purchase order ' . $order->order_code . ' status to ' . ucfirst(str_replace('_', ' ', $newStatus)) . '.',
            'link' => route('admin.suppliers.orders', ['search' => $order->order_code]),
        ]);

        return redirect()->back()
            ->with('success', 'Order ' . $order->order_code . ' workflow status updated to ' . ucfirst(str_replace('_', ' ', $newStatus)) . '.');
    }

    /**
     * Supplier profile and account settings.
     */
    public function profile()
    {
        $supplier = $this->getSupplier();
        $user = Auth::user();

        return view('supplier.profile', compact('supplier', 'user'));
    }

    /**
     * Update supplier profile info.
     */
    public function updateProfile(Request $request)
    {
        $supplier = $this->getSupplier();
        $user = Auth::user();

        $validated = $request->validate([
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        $supplier->update([
            'contact_person' => $validated['contact_person'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
        ]);

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'The provided current password does not match.']);
            }
            $user->password = Hash::make($validated['new_password']);
            $user->save();
        }

        return redirect()->back()->with('success', 'Supplier organization settings and credentials updated successfully.');
    }

    /**
     * Mark notifications as read.
     */
    public function markNotificationsRead()
    {
        $supplier = $this->getSupplier();
        $supplier->notifications()->update(['is_read' => true]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
