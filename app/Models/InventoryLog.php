<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'project_id',
        'transaction_type',
        'quantity',
        'unit_cost',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'float',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getTransactionBadgeAttribute(): array
    {
        return match ($this->transaction_type) {
            'excess_return' => [
                'label' => 'Excess Material Returned',
                'color' => '#10b981',
                'bg' => 'rgba(16, 185, 129, 0.15)',
                'border' => 'rgba(16, 185, 129, 0.3)',
                'icon' => '',
            ],
            'allocation' => [
                'label' => 'Site BOM Allocation',
                'color' => '#38bdf8',
                'bg' => 'rgba(56, 189, 248, 0.15)',
                'border' => 'rgba(56, 189, 248, 0.3)',
                'icon' => '',
            ],
            'usage' => [
                'label' => 'Site Consumption Recorded',
                'color' => '#f59e0b',
                'bg' => 'rgba(245, 158, 11, 0.15)',
                'border' => 'rgba(245, 158, 11, 0.3)',
                'icon' => '',
            ],
            'restock' => [
                'label' => 'Warehouse Restock / PO',
                'color' => '#8b5cf6',
                'bg' => 'rgba(139, 92, 246, 0.15)',
                'border' => 'rgba(139, 92, 246, 0.3)',
                'icon' => '',
            ],
            default => [
                'label' => 'Inventory Adjustment',
                'color' => '#94a3b8',
                'bg' => 'rgba(148, 163, 184, 0.15)',
                'border' => 'rgba(148, 163, 184, 0.3)',
                'icon' => '',
            ],
        };
    }
}
