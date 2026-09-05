<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'material_id',
        'allocated_qty',
        'used_qty',
        'excess_returned_qty',
        'unit_price',
    ];

    protected $casts = [
        'allocated_qty' => 'integer',
        'used_qty' => 'integer',
        'excess_returned_qty' => 'integer',
        'unit_price' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function dailyUsages()
    {
        return $this->hasMany(DailyMaterialUsage::class, 'project_material_id')->orderBy('usage_date', 'desc');
    }

    // Remaining unconsumed site stock available for return or ongoing construction
    public function getRemainingQtyAttribute(): int
    {
        return max(0, $this->allocated_qty - $this->used_qty - $this->excess_returned_qty);
    }

    // Net materials allocated to project after subtracting excess returned to inventory
    public function getNetAllocatedQtyAttribute(): int
    {
        return max(0, $this->allocated_qty - $this->excess_returned_qty);
    }

    public function getTotalCostAttribute(): float
    {
        return round($this->allocated_qty * $this->unit_price, 2);
    }

    public function getNetCostAttribute(): float
    {
        return round($this->net_allocated_qty * $this->unit_price, 2);
    }

    public function getUsedCostAttribute(): float
    {
        return round($this->used_qty * $this->unit_price, 2);
    }

    public function getReturnedExcessValueAttribute(): float
    {
        return round($this->excess_returned_qty * $this->unit_price, 2);
    }
}
