<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierOrderLog;
use App\Models\SupplierNotification;
use App\Models\SupplierOrderMessage;
use App\Models\SupplierInquiry;
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

        // 1. Material Catalog Statistics (Section 7)
        $totalMaterials = $supplier->materials()->count();
        $availableMaterials = $supplier->materials()->where('is_active', true)->where('availability_status', 'available')->count();
        $unavailableMaterials = $supplier->materials()->where(function ($q) {
            $q->where('is_active', false)->orWhere('availability_status', 'unavailable');
        })->count();

        // 2. Orders Statistics (Section 7)
        $pendingOrders = $supplier->orders()->where('status', 'pending')->count();
        $activeOrders = $supplier->orders()->whereIn('status', ['confirmed', 'processing', 'ready_for_delivery'])->count();
        $completedOrders = $supplier->orders()->whereIn('status', ['delivered', 'completed'])->count();
        $totalRevenue = $supplier->orders()->whereIn('status', ['delivered', 'completed'])->sum('total_amount');

        // 3. Incoming & Recent Purchase Orders
        $recentOrders = $supplier->orders()->with(['items', 'orderedBy', 'project'])->latest()->take(8)->get();
        $recentNotifications = $supplier->notifications()->latest()->take(5)->get();
        $catalogHighlights = $supplier->materials()->where('is_active', true)->orderBy('name', 'asc')->take(6)->get();

        return view('supplier.dashboard', compact(
            'supplier',
            'totalMaterials',
            'availableMaterials',
            'unavailableMaterials',
            'pendingOrders',
            'activeOrders',
            'completedOrders',
            'totalRevenue',
            'recentOrders',
            'recentNotifications',
            'catalogHighlights'
        ));
    }

    /**
     * My Products & Price Catalog Management.
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

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'available') {
                $query->where('availability_status', 'available')->where('is_active', true);
            } elseif ($request->status === 'unavailable') {
                $query->where(function ($q) {
                    $q->where('availability_status', 'unavailable')->orWhere('is_active', false);
                });
            }
        }

        $materials = $query->orderBy('name', 'asc')->paginate(12)->withQueryString();

        // Get unique subcategories for filter pills
        $subcategories = $supplier->materials()->whereNotNull('subcategory')->distinct()->pluck('subcategory');

        return view('supplier.materials', compact('supplier', 'materials', 'subcategories'));
    }

    /**
     * Store a new product in the catalog.
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
            'unit_price' => 'required|numeric|min:0',
            'min_order_qty' => 'nullable|integer|min:1',
            'availability_status' => 'nullable|in:available,unavailable',
            'image' => 'nullable|image|max:4096',
        ]);

        // Generate Product Code
        $prefix = match ($supplier->category) {
            'Windows & Doors' => 'MAT-WNDR',
            'Roofing' => 'MAT-ROOF',
            'Structural & Masonry' => 'MAT-STRC',
            default => 'MAT-SUP',
        };

        $count = $supplier->materials()->count() + 1;
        $materialCode = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(3));

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('materials', 'public');
            $imageUrl = '/storage/' . $path;
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
            'available_quantity' => 999999,
            'unit_price' => $validated['unit_price'],
            'min_order_qty' => $validated['min_order_qty'] ?? 1,
            'availability_status' => $validated['availability_status'] ?? 'available',
            'image_url' => $imageUrl,
            'is_active' => true,
        ]);

        return redirect()->route('supplier.materials')
            ->with('success', 'Product "' . $material->name . '" has been added to your catalog under ' . $material->material_code . '.');
    }

    /**
     * Update an existing product in the catalog.
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
            'unit_price' => 'required|numeric|min:0',
            'min_order_qty' => 'nullable|integer|min:1',
            'availability_status' => 'required|in:available,unavailable',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:4096',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'subcategory' => $validated['subcategory'] ?? $material->subcategory,
            'description' => $validated['description'] ?? null,
            'specifications' => $validated['specifications'] ?? null,
            'unit' => $validated['unit'],
            'unit_price' => $validated['unit_price'],
            'min_order_qty' => $validated['min_order_qty'] ?? 1,
            'availability_status' => $validated['availability_status'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('materials', 'public');
            $updateData['image_url'] = '/storage/' . $path;
        }

        $material->update($updateData);

        return redirect()->back()
            ->with('success', 'Product "' . $material->name . '" specifications and unit price updated.');
    }

    /**
     * Delete or de-list product.
     */
    public function destroyMaterial($id)
    {
        $supplier = $this->getSupplier();
        $material = $supplier->materials()->findOrFail($id);

        if ($material->orderItems()->exists()) {
            $material->update(['is_active' => false, 'availability_status' => 'unavailable']);
            return redirect()->back()->with('success', 'Product "' . $material->name . '" is linked to previous purchase orders and has been set to Unavailable.');
        }

        $name = $material->name;
        $material->delete();

        return redirect()->back()->with('success', 'Product "' . $name . '" removed from catalog.');
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
     * Show single order details.
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
     * Advance order status workflow.
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
            $order->syncToInventory();
        }
        $order->save();

        // Record Audit Log
        SupplierOrderLog::create([
            'supplier_order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'comment' => $validated['comment'] ?? ('Supplier updated order status to ' . ucfirst(str_replace('_', ' ', $newStatus))),
        ]);

        // Notify Admins
        SupplierNotification::create([
            'supplier_id' => $supplier->id,
            'user_id' => null,
            'type' => 'order_status_updated',
            'title' => 'Order ' . $order->order_code . ' Status: ' . ucfirst(str_replace('_', ' ', $newStatus)),
            'message' => $supplier->name . ' updated order ' . $order->order_code . ' to ' . ucfirst(str_replace('_', ' ', $newStatus)) . '.',
            'link' => route('admin.suppliers.orders', ['search' => $order->order_code]),
        ]);

        $syncMsg = ($newStatus === 'delivered' || $newStatus === 'completed') ? ' Materials successfully transferred and credited into Admin Materials Inventory.' : '';

        return redirect()->back()
            ->with('success', 'Order ' . $order->order_code . ' workflow status updated to ' . ucfirst(str_replace('_', ' ', $newStatus)) . '.' . $syncMsg);
    }

    /**
     * Supplier profile and settings.
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

    /**
     * Post a new message on a Purchase Order (Supplier to Admin).
     */
    public function sendMessage(Request $request, $id)
    {
        $supplier = $this->getSupplier();
        $order = SupplierOrder::where('supplier_id', $supplier->id)->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $msg = SupplierOrderMessage::create([
            'supplier_order_id' => $order->id,
            'user_id' => Auth::id(),
            'sender_role' => 'supplier',
            'message' => trim($validated['message']),
        ]);

        // Dispatch alert notification to Master Admin
        SupplierNotification::create([
            'supplier_id' => $supplier->id,
            'user_id' => null,
            'type' => 'order_message',
            'title' => 'New Message from ' . $supplier->name . ' on ' . $order->order_code,
            'message' => $supplier->name . ': ' . Str::limit($validated['message'], 100),
            'link' => route('admin.suppliers.orders', ['search' => $order->order_code]),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg->load('user'),
                'formatted_time' => $msg->created_at->format('M d, Y h:i A'),
            ]);
        }

        return redirect()->back()->with('success', 'Message dispatched to St. Bilfrid Procurement Team.');
    }

    /**
     * Get all chat messages for a specific Purchase Order.
     */
    public function getMessages($id)
    {
        $supplier = $this->getSupplier();
        $order = SupplierOrder::where('supplier_id', $supplier->id)->findOrFail($id);

        $messages = $order->messages()->with('user')->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'sender_role' => $m->sender_role,
                'user_name' => $m->user ? $m->user->name : 'System',
                'message' => $m->message,
                'time' => $m->created_at->format('M d, Y h:i A'),
                'is_supplier' => $m->sender_role === 'supplier',
            ];
        });

        return response()->json([
            'success' => true,
            'order_code' => $order->order_code,
            'messages' => $messages,
        ]);
    }

    /**
     * Inquiries list for Supplier.
     */
    public function inquiries(Request $request)
    {
        $supplier = $this->getSupplier();
        $inquiries = SupplierInquiry::with(['material', 'user'])
            ->where('supplier_id', $supplier->id)
            ->latest()
            ->paginate(15);

        return view('supplier.inquiries', compact('supplier', 'inquiries'));
    }

    /**
     * Respond to a Material Inquiry with quotation / details.
     */
    public function respondInquiry(Request $request, $id)
    {
        $supplier = $this->getSupplier();
        $inquiry = SupplierInquiry::where('supplier_id', $supplier->id)->findOrFail($id);

        $validated = $request->validate([
            'supplier_response' => 'required|string|max:2000',
            'quoted_unit_price' => 'nullable|numeric|min:0',
        ]);

        $inquiry->update([
            'supplier_response' => $validated['supplier_response'],
            'quoted_unit_price' => $validated['quoted_unit_price'] ?? $inquiry->quoted_unit_price,
            'status' => 'quoted',
            'responded_at' => now(),
        ]);

        // Dispatch alert notification to admin
        SupplierNotification::create([
            'supplier_id' => $supplier->id,
            'user_id' => null,
            'type' => 'inquiry_response',
            'title' => 'Quotation Response from ' . $supplier->name,
            'message' => $supplier->name . ' responded to inquiry "' . $inquiry->subject . '": ' . Str::limit($validated['supplier_response'], 100),
            'link' => route('admin.suppliers.materials'),
        ]);

        return redirect()->back()->with('success', 'Quotation response sent to St. Bilfrid Dev. Corp.');
    }
}
