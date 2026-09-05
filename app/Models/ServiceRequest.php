<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code',
        'client_name',
        'client_email',
        'client_phone',
        'service_type',
        'land_area_sqm',
        'floor_area_sqm',
        'estimated_cost',
        'requested_start_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'requested_start_date' => 'date',
        'land_area_sqm' => 'float',
        'floor_area_sqm' => 'float',
        'estimated_cost' => 'float',
    ];
}
