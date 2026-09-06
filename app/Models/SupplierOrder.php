<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class SupplierOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'supplier_id',
        'ordered_by_user_id',
        'project_id',
        'delivery_location',
        'requested_delivery_date',
        'actual_delivery_date',
        'total_amount',
        'status',
        'is_synced_to_inventory',
        'notes',
    ];

    protected $casts = [
        'requested_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'is_synced_to_inventory' => 'boolean',
    ];

    /**
     * Parent supplier.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * User who placed the order.
     */
    public function orderedBy()
    {
        return $this->belongsTo(User::class, 'ordered_by_user_id');
    }

    /**
     * Associated construction project (if directly tied).
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Order line items.
     */
    public function items()
    {
        return $this->hasMany(SupplierOrderItem::class);
    }

    /**
     * Status transition audit logs.
     */
    public function logs()
    {
        return $this->hasMany(SupplierOrderLog::class)->latest();
    }

    /**
     * Interactive order communication messages.
     */
    public function messages()
    {
        return $this->hasMany(SupplierOrderMessage::class)->with('user')->orderBy('created_at', 'asc');
    }

    /**
     * Synchronize delivered products into Central Materials Inventory (and Project Materials if linked).
     */
    public function syncToInventory(): bool
    {
        if ($this->is_synced_to_inventory) {
            return false; // already synced to prevent duplicate increments
        }

        $this->loadMissing(['items.material', 'supplier', 'project']);

        foreach ($this->items as $item) {
            // Find or create in Material inventory
            $material = Material::where('name', $item->material_name)->first();

            if (!$material) {
                $codePrefix = match($this->supplier->category ?? '') {
                    'Windows & Doors' => 'MAT-WNDR-',
                    'Roofing' => 'MAT-ROOF-',
                    'Structural & Masonry' => 'MAT-STRC-',
                    default => 'MAT-SUP-',
                };
                $code = $codePrefix . strtoupper(substr(uniqid(), -5));

                $material = Material::create([
                    'material_code' => $code,
                    'name' => $item->material_name,
                    'category' => $this->supplier->category ?? 'General',
                    'unit' => $item->unit,
                    'unit_cost' => $item->unit_price,
                    'stock_quantity' => $item->quantity,
                ]);
            } else {
                $material->increment('stock_quantity', $item->quantity);
                $material->unit_cost = $item->unit_price;
                $material->save();
            }

            // Record official inventory movement in InventoryLog
            InventoryLog::create([
                'material_id' => $material->id,
                'project_id' => $this->project_id,
                'transaction_type' => 'restock',
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_price,
                'reference_no' => $this->order_code,
                'notes' => 'Trade Supplier Delivery Receipt from ' . ($this->supplier->name ?? 'Trade Supplier') . ($this->project ? ' for site ' . $this->project->title : ' to Central Warehouse Depot') . '.',
            ]);

            // If directly assigned to a Project, also sync with Project Materials BOM
            if ($this->project_id) {
                $projectMat = ProjectMaterial::firstOrNew([
                    'project_id' => $this->project_id,
                    'material_id' => $material->id,
                ]);

                $projectMat->allocated_qty = ($projectMat->allocated_qty ?? 0) + $item->quantity;
                $projectMat->unit_price = $item->unit_price;
                $projectMat->save();
            }
        }

        $this->is_synced_to_inventory = true;
        $this->save();

        return true;
    }

    /**
     * Format status badge.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending' => ['label' => 'Pending Approval', 'bg' => 'rgba(245, 158, 11, 0.15)', 'color' => '#f59e0b', 'border' => 'rgba(245, 158, 11, 0.3)'],
            'confirmed' => ['label' => 'Confirmed', 'bg' => 'rgba(56, 189, 248, 0.15)', 'color' => '#38bdf8', 'border' => 'rgba(56, 189, 248, 0.3)'],
            'processing' => ['label' => 'Processing', 'bg' => 'rgba(129, 140, 248, 0.15)', 'color' => '#818cf8', 'border' => 'rgba(129, 140, 248, 0.3)'],
            'ready_for_delivery' => ['label' => 'Ready for Delivery', 'bg' => 'rgba(236, 72, 153, 0.15)', 'color' => '#ec4899', 'border' => 'rgba(236, 72, 153, 0.3)'],
            'delivered' => ['label' => 'Delivered', 'bg' => 'rgba(16, 185, 129, 0.15)', 'color' => '#10b981', 'border' => 'rgba(16, 185, 129, 0.3)'],
            'completed' => ['label' => 'Completed', 'bg' => 'rgba(34, 197, 94, 0.2)', 'color' => '#22c55e', 'border' => 'rgba(34, 197, 94, 0.4)'],
            'cancelled' => ['label' => 'Cancelled', 'bg' => 'rgba(239, 68, 68, 0.15)', 'color' => '#ef4444', 'border' => 'rgba(239, 68, 68, 0.3)'],
            default => ['label' => ucfirst(str_replace('_', ' ', $this->status)), 'bg' => 'rgba(148, 163, 184, 0.15)', 'color' => '#94a3b8', 'border' => 'rgba(148, 163, 184, 0.3)'],
        };
    }
}
