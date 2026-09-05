<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectScopeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'item_number',
        'item_name',
        'volume_or_area',
        'notes',
        'materials_subtotal',
        'labor_subtotal',
        'equipment_subtotal',
        'direct_cost',
        'contingency_percent',
        'contingency_amount',
        'taxes_percent',
        'taxes_amount',
        'profit_percent',
        'profit_amount',
        'total_item_cost',
    ];

    protected $casts = [
        'materials_subtotal' => 'float',
        'labor_subtotal' => 'float',
        'equipment_subtotal' => 'float',
        'direct_cost' => 'float',
        'contingency_percent' => 'float',
        'contingency_amount' => 'float',
        'taxes_percent' => 'float',
        'taxes_amount' => 'float',
        'profit_percent' => 'float',
        'profit_amount' => 'float',
        'total_item_cost' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function lines()
    {
        return $this->hasMany(ProjectScopeLine::class, 'project_scope_item_id');
    }

    public function materials()
    {
        return $this->hasMany(ProjectScopeLine::class, 'project_scope_item_id')->where('category', 'material');
    }

    public function labors()
    {
        return $this->hasMany(ProjectScopeLine::class, 'project_scope_item_id')->where('category', 'labor');
    }

    public function equipments()
    {
        return $this->hasMany(ProjectScopeLine::class, 'project_scope_item_id')->where('category', 'equipment');
    }

    /**
     * Auto-recalculate direct costs, markups (Contingency, Taxes, Profit), and total item cost.
     */
    public function recalculate(): void
    {
        $matSub = (float) $this->materials()->sum('total_cost');
        $labSub = (float) $this->labors()->sum('total_cost');
        $eqSub  = (float) $this->equipments()->sum('total_cost');

        $direct = $matSub + $labSub + $eqSub;

        $cPercent = (float) ($this->contingency_percent ?? 0);
        $tPercent = (float) ($this->taxes_percent ?? 0);
        $pPercent = (float) ($this->profit_percent ?? 0);

        $cAmt = (float) ($this->contingency_amount > 0 ? $this->contingency_amount : ($cPercent > 0 ? round($direct * ($cPercent / 100)) : 0));
        $tAmt = (float) ($this->taxes_amount > 0 ? $this->taxes_amount : ($tPercent > 0 ? round($direct * ($tPercent / 100)) : 0));
        $pAmt = (float) ($this->profit_amount > 0 ? $this->profit_amount : ($pPercent > 0 ? round($direct * ($pPercent / 100)) : 0));

        $total = $direct + $cAmt + $tAmt + $pAmt;

        $this->update([
            'materials_subtotal' => $matSub,
            'labor_subtotal' => $labSub,
            'equipment_subtotal' => $eqSub,
            'direct_cost' => $direct,
            'contingency_amount' => $cAmt,
            'taxes_amount' => $tAmt,
            'profit_amount' => $pAmt,
            'total_item_cost' => $total,
        ]);
    }
}
