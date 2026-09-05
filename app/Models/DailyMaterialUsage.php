<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMaterialUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_material_id',
        'material_id',
        'usage_date',
        'quantity_used',
        'activity_description',
        'logged_by',
        'notes',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'quantity_used' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectMaterial()
    {
        return $this->belongsTo(ProjectMaterial::class, 'project_material_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
