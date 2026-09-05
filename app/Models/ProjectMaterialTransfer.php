<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMaterialTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_project_id',
        'destination_project_id',
        'material_id',
        'quantity_transferred',
        'transfer_date',
        'transfer_reference_no',
        'transfer_type',
        'reason',
        'authorized_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'quantity_transferred' => 'float',
    ];

    public function sourceProject()
    {
        return $this->belongsTo(Project::class, 'source_project_id');
    }

    public function destinationProject()
    {
        return $this->belongsTo(Project::class, 'destination_project_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
