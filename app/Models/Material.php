<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_code',
        'name',
        'category',
        'unit',
        'unit_cost',
        'stock_quantity',
        'is_new_product',
        'last_purchased_at',
    ];

    protected $casts = [
        'unit_cost' => 'float',
        'stock_quantity' => 'integer',
        'is_new_product' => 'boolean',
        'last_purchased_at' => 'datetime',
    ];

    public function projectMaterials()
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class)->orderBy('created_at', 'desc');
    }
}
