<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectScopeLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_scope_item_id',
        'category',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total_cost',
        'material_id',
        'used_quantity',
        'excess_returned_quantity',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'float',
        'total_cost' => 'float',
        'used_quantity' => 'float',
        'excess_returned_quantity' => 'float',
    ];

    public function scopeItem()
    {
        return $this->belongsTo(ProjectScopeItem::class, 'project_scope_item_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function getRemainingQuantityAttribute(): float
    {
        return max(0, $this->quantity - $this->used_quantity - $this->excess_returned_quantity);
    }
}
