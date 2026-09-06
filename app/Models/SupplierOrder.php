<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'notes',
    ];

    protected $casts = [
        'requested_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
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
