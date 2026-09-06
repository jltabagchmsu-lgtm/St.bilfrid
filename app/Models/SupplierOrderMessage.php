<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrderMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_order_id',
        'user_id',
        'sender_role',
        'message',
        'attachment_url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
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
