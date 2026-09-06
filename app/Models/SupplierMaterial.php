<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'material_code',
        'name',
        'category',
        'subcategory',
        'description',
        'specifications',
        'unit',
        'available_quantity',
        'unit_price',
        'min_order_qty',
        'availability_status',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'available_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'min_order_qty' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get parent supplier.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Order items referencing this material.
     */
    public function orderItems()
    {
        return $this->hasMany(SupplierOrderItem::class);
    }

    /**
     * Automatically update availability status based on stock level if applicable.
     */
    public function syncAvailabilityStatus(): void
    {
        if ($this->available_quantity <= 0) {
            $this->availability_status = 'out_of_stock';
        } elseif ($this->available_quantity <= 10) {
            $this->availability_status = 'low_stock';
        } else {
            $this->availability_status = 'available';
        }
    }

    /**
     * Status badge styling class.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->availability_status) {
            'available' => ['label' => 'Available', 'bg' => 'rgba(16, 185, 129, 0.15)', 'color' => '#10b981', 'border' => 'rgba(16, 185, 129, 0.3)'],
            'low_stock' => ['label' => 'Low Stock', 'bg' => 'rgba(245, 158, 11, 0.15)', 'color' => '#f59e0b', 'border' => 'rgba(245, 158, 11, 0.3)'],
            'out_of_stock' => ['label' => 'Out of Stock', 'bg' => 'rgba(239, 68, 68, 0.15)', 'color' => '#ef4444', 'border' => 'rgba(239, 68, 68, 0.3)'],
            'unavailable' => ['label' => 'Unavailable', 'bg' => 'rgba(148, 163, 184, 0.15)', 'color' => '#94a3b8', 'border' => 'rgba(148, 163, 184, 0.3)'],
            default => ['label' => ucfirst(str_replace('_', ' ', $this->availability_status)), 'bg' => 'rgba(148, 163, 184, 0.15)', 'color' => '#94a3b8', 'border' => 'rgba(148, 163, 184, 0.3)'],
        };
    }
}
