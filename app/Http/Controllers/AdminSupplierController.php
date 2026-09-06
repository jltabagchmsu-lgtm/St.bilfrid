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
use App\Models\Project;
use App\Models\Material;
use App\Models\ProjectMaterial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminSupplierController extends Controller
{
    /**
     * Centralized Supplier Management Hub for Admin.
     */
    public function index()
    {
        // 1. Fetch the 3 primary suppliers (Mils Glass and Aluminum Works, Colorsteel, Titan Structural & Steel)
        $suppliers = Supplier::withCount(['materials', 'orders'])->get();

        // 2. Aggregate KPI Metrics (Section 11)
        $totalSuppliers = $suppliers->count();
        $totalAvailableMaterials = SupplierMaterial::where('is_active', true)->where('availability_status', 'available')->count();
        $pendingOrders = SupplierOrder::where('status', 'pending')->count();
        $activeOrders = SupplierOrder::whereIn('status', ['confirmed', 'processing', 'ready_for_delivery'])->count();
        $completedOrders = SupplierOrder::whereIn('status', ['delivered', 'completed'])->count();
        $totalProcurementCost = SupplierOrder::whereIn('status', ['delivered', 'completed'])->sum('total_amount');

        // 3. Recent Orders Feed
        $recentOrders = SupplierOrder::with(['supplier', 'items', 'project'])->latest()->take(8)->get();

        // 4. Active Projects for Order Generator
        $activeProjects = Project::whereIn('status', ['approved', 'in_progress'])->orderBy('title', 'asc')->get();

        // 5. Featured product catalog items for PO placement
        $featuredMaterials = SupplierMaterial::with('supplier')->where('is_active', true)->where('availability_status', 'available')->orderBy('name', 'asc')->get();

        return view('admin.suppliers.index', compact(
            'suppliers',
            'totalSuppliers',
            'totalAvailableMaterials',
            'pendingOrders',
            'activeOrders',
            'completedOrders',
            'totalProcurementCost',
            'recentOrders',
            'activeProjects',
            'featuredMaterials'
        ));
    }

    /**
     * Cross-Supplier Materials Browser & Comparison.
     */
    public function materials(Request $request)
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $activeProjects = Project::whereIn('status', ['approved', 'in_progress'])->orderBy('title', 'asc')->get();

        $query = SupplierMaterial::with('supplier')->where('is_active', true);

        // Supplier filter
        if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Category filter (Windows & Doors, Roofing, Structural & Masonry)
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Subcategory filter
        if ($request->filled('subcategory') && $request->subcategory !== 'all') {
            $query->where('subcategory', $request->subcategory);
        }

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('material_code', 'like', "%{$search}%")
                  ->orWhere('specifications', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Availability filter
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'available') {
                $query->where('availability_status', 'available');
            } elseif ($request->status === 'unavailable') {
                $query->where('availability_status', 'unavailable');
            }
        }

        // Sorting
        $sort = $request->get('sort', 'name_asc');
        match ($sort) {
            'price_low' => $query->orderBy('unit_price', 'asc'),
            'price_high' => $query->orderBy('unit_price', 'desc'),
            'newest' => $query->latest(),
            default => $query->orderBy('name', 'asc'),
        };

        $materials = $query->paginate(15)->withQueryString();

        // Subcategories list for active category
        $subcategories = SupplierMaterial::where('is_active', true)
            ->when($request->filled('category') && $request->category !== 'all', function ($q) use ($request) {
                $q->where('category', $request->category);
            })
            ->distinct()
            ->pluck('subcategory');

        return view('admin.suppliers.materials', compact('suppliers', 'materials', 'activeProjects', 'subcategories'));
    }

    /**
     * Master Purchase Order Tracker.
     */
    public function orders(Request $request)
    {
        $suppliers = Supplier::all();
        $activeProjects = Project::orderBy('title', 'asc')->get();

        $query = SupplierOrder::with(['supplier', 'items.material', 'project', 'orderedBy', 'logs.user']);

        if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id') && $request->project_id !== 'all') {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhere('delivery_location', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(12)->withQueryString();

        return view('admin.suppliers.orders', compact('suppliers', 'orders', 'activeProjects'));
    }

    /**
     * Create / Place a new Purchase Order to a Supplier.
     */
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'project_id' => 'nullable|exists:projects,id',
            'delivery_location' => 'required|string|max:255',
            'requested_delivery_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:supplier_materials,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $supplier = Supplier::findOrFail($validated['supplier_id']);

        // Generate Order Code (ORD-YYYY-XXXX)
        $year = date('Y');
        $orderCount = SupplierOrder::whereYear('created_at', $year)->count() + 1;
        $orderCode = 'ORD-' . $year . '-' . str_pad($orderCount, 4, '0', STR_PAD_LEFT);

        // Compute items and total price
        $totalAmount = 0;
        $orderItemsData = [];

        foreach ($validated['items'] as $itemData) {
            $material = SupplierMaterial::where('supplier_id', $supplier->id)->findOrFail($itemData['material_id']);
            $qty = (int) $itemData['quantity'];
            $unitPrice = (float) $material->unit_price;
            $lineTotal = $qty * $unitPrice;
            $totalAmount += $lineTotal;

            $orderItemsData[] = [
                'supplier_material_id' => $material->id,
                'material_name' => $material->name,
                'quantity' => $qty,
                'unit' => $material->unit,
                'unit_price' => $unitPrice,
                'total_price' => $lineTotal,
            ];
        }

        $order = SupplierOrder::create([
            'order_code' => $orderCode,
            'supplier_id' => $supplier->id,
            'ordered_by_user_id' => Auth::id(),
            'project_id' => $validated['project_id'] ?? null,
            'delivery_location' => $validated['delivery_location'],
            'requested_delivery_date' => $validated['requested_delivery_date'],
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($orderItemsData as $item) {
            $order->items()->create($item);
        }

        // Record initial status log
        SupplierOrderLog::create([
            'supplier_order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => 'created',
            'to_status' => 'pending',
            'comment' => 'Purchase order placed by Master Administrator for ' . $supplier->name . '.',
        ]);

        // Send Notification to Supplier
        SupplierNotification::create([
            'supplier_id' => $supplier->id,
            'user_id' => null,
            'type' => 'order_created',
            'title' => 'New Purchase Order Received: ' . $orderCode,
            'message' => 'St. Bilfrid Dev. Corp placed purchase order ' . $orderCode . ' totaling PHP ' . number_format($totalAmount, 2) . ' for ' . count($orderItemsData) . ' product(s).',
            'link' => route('supplier.orders', ['search' => $orderCode]),
        ]);

        return redirect()->route('admin.suppliers.orders')
            ->with('success', 'Purchase Order ' . $orderCode . ' successfully submitted to ' . $supplier->name . ' (Total: PHP ' . number_format($totalAmount, 2) . ').');
    }

    /**
     * Update order status from Admin side.
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $order = SupplierOrder::with('supplier')->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,ready_for_delivery,delivered,completed,cancelled',
            'comment' => 'nullable|string|max:500',
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        $order->status = $newStatus;
        if ($newStatus === 'delivered' || $newStatus === 'completed') {
            $order->actual_delivery_date = now();
            $order->syncToInventory();
        }
        $order->save();

        SupplierOrderLog::create([
            'supplier_order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'comment' => $validated['comment'] ?? ('Status updated by Master Administrator to ' . ucfirst(str_replace('_', ' ', $newStatus))),
        ]);

        // Notify Supplier
        SupplierNotification::create([
            'supplier_id' => $order->supplier_id,
            'user_id' => null,
            'type' => 'order_status_updated',
            'title' => 'Admin Status Update on ' . $order->order_code,
            'message' => 'Administrator updated order ' . $order->order_code . ' status to ' . ucfirst(str_replace('_', ' ', $newStatus)) . '.',
            'link' => route('supplier.orders', ['search' => $order->order_code]),
        ]);

        $syncMsg = ($newStatus === 'delivered' || $newStatus === 'completed') ? ' Materials automatically synchronized into Admin Inventory.' : '';

        return redirect()->back()
            ->with('success', 'Order ' . $order->order_code . ' status updated to ' . ucfirst(str_replace('_', ' ', $newStatus)) . '.' . $syncMsg);
    }

    /**
     * 1-Click Receive Delivered Order into Project BOM or Central Stock.
     */
    public function receiveOrder(Request $request, $id)
    {
        $order = SupplierOrder::with(['items.material', 'supplier', 'project'])->findOrFail($id);

        $order->status = 'completed';
        $order->actual_delivery_date = now();
        $order->save();

        // Synchronize into inventory & BOM
        $order->syncToInventory();

        SupplierOrderLog::create([
            'supplier_order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => $order->status,
            'to_status' => 'completed',
            'comment' => 'Products officially received, verified on-site, and accepted into inventory.',
        ]);

        return redirect()->back()->with('success', 'Order ' . $order->order_code . ' marked as Completed. Delivered products successfully synchronized into Admin Materials Inventory.');
    }

    /**
     * Update Supplier Profile / Settings from Admin.
     */
    public function updateSupplier(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'rating' => 'nullable|numeric|min:1|max:5',
            'status' => 'required|in:active,inactive',
        ]);

        $supplier->update($validated);

        return redirect()->back()
            ->with('success', 'Supplier organization record for "' . $supplier->name . '" updated successfully.');
    }

    /**
     * Toggle Supplier Account Active / Inactive.
     */
    public function toggleSupplierStatus($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->status = $supplier->status === 'active' ? 'inactive' : 'active';
        $supplier->save();

        $state = $supplier->status === 'active' ? 'Activated' : 'Deactivated';

        return redirect()->back()
            ->with('success', 'Supplier account for ' . $supplier->name . ' has been ' . $state . '.');
    }

    /**
     * Post a new message on a Purchase Order (Admin to Supplier).
     */
    public function sendMessage(Request $request, $id)
    {
        $order = SupplierOrder::with('supplier')->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $msg = SupplierOrderMessage::create([
            'supplier_order_id' => $order->id,
            'user_id' => Auth::id(),
            'sender_role' => 'admin',
            'message' => trim($validated['message']),
        ]);

        // Dispatch alert notification to the supplier
        SupplierNotification::create([
            'supplier_id' => $order->supplier_id,
            'user_id' => null,
            'type' => 'order_message',
            'title' => 'New Message on ' . $order->order_code,
            'message' => Auth::user()->name . ' (Procurement Admin): ' . Str::limit($validated['message'], 100),
            'link' => route('supplier.orders', ['search' => $order->order_code]),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg->load('user'),
                'formatted_time' => $msg->created_at->format('M d, Y h:i A'),
            ]);
        }

        return redirect()->back()->with('success', 'Message sent to ' . $order->supplier->name . '.');
    }

    /**
     * Get all chat messages for a specific Purchase Order.
     */
    public function getMessages($id)
    {
        $order = SupplierOrder::findOrFail($id);
        $messages = $order->messages()->with('user')->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'sender_role' => $m->sender_role,
                'user_name' => $m->user ? $m->user->name : 'System',
                'message' => $m->message,
                'time' => $m->created_at->format('M d, Y h:i A'),
                'is_admin' => $m->sender_role === 'admin',
            ];
        });

        return response()->json([
            'success' => true,
            'order_code' => $order->order_code,
            'messages' => $messages,
        ]);
    }

    /**
     * Submit an Inquiry or Request for Quotation to a Supplier.
     */
    public function storeInquiry(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_material_id' => 'nullable|exists:supplier_materials,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'requested_quantity' => 'nullable|integer|min:1',
        ]);

        $inquiry = SupplierInquiry::create([
            'supplier_id' => $validated['supplier_id'],
            'supplier_material_id' => $validated['supplier_material_id'] ?? null,
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'requested_quantity' => $validated['requested_quantity'] ?? null,
            'status' => 'open',
        ]);

        $supplier = Supplier::findOrFail($validated['supplier_id']);

        // Dispatch alert notification to the supplier
        SupplierNotification::create([
            'supplier_id' => $supplier->id,
            'user_id' => null,
            'type' => 'inquiry_received',
            'title' => 'New Material Inquiry: ' . $validated['subject'],
            'message' => 'St. Bilfrid Dev. Corp submitted a material inquiry / quotation request: ' . Str::limit($validated['message'], 100),
            'link' => route('supplier.dashboard'),
        ]);

        return redirect()->back()->with('success', 'Inquiry / Quotation Request successfully dispatched to ' . $supplier->name . '.');
    }
}
