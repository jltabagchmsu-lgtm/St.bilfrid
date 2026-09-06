<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_order_id',
        'user_id',
        'from_status',
        'to_status',
        'comment',
    ];

    public function order()
    {
        return $this->belongsTo(SupplierOrder::class, 'supplier_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
