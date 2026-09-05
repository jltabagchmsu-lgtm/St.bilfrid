<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_task_id',
        'material_id',
        'material_name',
        'category',
        'unit',
        'unit_cost',
        'quantity',
        'total_cost',
        'status',
    ];

    protected $casts = [
        'unit_cost' => 'float',
        'quantity' => 'float',
        'total_cost' => 'float',
    ];

    public function projectTask()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    // Auto-calculate total cost on save
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->total_cost = round($model->quantity * $model->unit_cost, 2);
        });
    }
}
