<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'cost_code',
        'cost_category',
        'item_name',
        'cost_type',
        'quantity',
        'unit',
        'unit_rate',
        'estimated_cost',
        'actual_cost',
        'status',
        'cost_date',
        'vendor_payee',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'cost_date' => 'date',
        'quantity' => 'float',
        'unit_rate' => 'float',
        'estimated_cost' => 'float',
        'actual_cost' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Cost Variance (Estimated - Actual). Positive means under-budget, negative means cost overrun.
     */
    public function getVarianceAttribute(): float
    {
        return round($this->estimated_cost - $this->actual_cost, 2);
    }

    /**
     * Variance percentage
     */
    public function getVariancePercentAttribute(): float
    {
        if ($this->estimated_cost <= 0) return 0;
        return round((($this->estimated_cost - $this->actual_cost) / $this->estimated_cost) * 100, 1);
    }
}
