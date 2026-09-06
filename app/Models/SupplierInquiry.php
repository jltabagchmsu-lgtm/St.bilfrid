<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'supplier_material_id',
        'user_id',
        'subject',
        'message',
        'requested_quantity',
        'status',
        'supplier_response',
        'quoted_unit_price',
        'responded_at',
    ];

    protected $casts = [
        'requested_quantity' => 'integer',
        'quoted_unit_price' => 'decimal:2',
        'responded_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function material()
    {
        return $this->belongsTo(SupplierMaterial::class, 'supplier_material_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
