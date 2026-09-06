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
     * Status badge styling class (Strictly Available / Unavailable).
     */
    public function getStatusBadgeAttribute(): array
    {
        if (!$this->is_active || $this->availability_status === 'unavailable') {
            return [
                'label' => 'Unavailable',
                'bg' => 'rgba(148, 163, 184, 0.15)',
                'color' => '#94a3b8',
                'border' => 'rgba(148, 163, 184, 0.3)',
            ];
        }

        return [
            'label' => 'Available',
            'bg' => 'rgba(16, 185, 129, 0.15)',
            'color' => '#10b981',
            'border' => 'rgba(16, 185, 129, 0.3)',
        ];
    }
}
