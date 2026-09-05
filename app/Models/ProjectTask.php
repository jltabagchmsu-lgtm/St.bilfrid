<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_name',
        'category',
        'assigned_personnel_id',
        'start_date',
        'due_date',
        'allocated_budget',
        'actual_cost',
        'progress',
        'sort_order',
        'status',
        'timeline_phase',
        'timeline_month',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'allocated_budget' => 'float',
        'actual_cost' => 'float',
        'progress' => 'integer',
        'sort_order' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedPersonnel()
    {
        return $this->belongsTo(Personnel::class, 'assigned_personnel_id');
    }

    public function taskMaterials()
    {
        return $this->hasMany(ProjectTaskMaterial::class, 'project_task_id');
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->progress >= 100 || $this->status === 'completed';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->progress >= 100 || $this->status === 'completed') {
            return 'completed';
        }
        if ($this->progress > 15 || $this->status === 'in_progress') {
            return 'in_progress';
        }
        if ($this->progress > 0 || $this->status === 'started') {
            return 'pending';
        }
        return 'overdue'; // gray / neutral
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->progress >= 100 || $this->status === 'completed') {
            return 'Completed';
        }
        if ($this->progress > 15 || $this->status === 'in_progress') {
            return 'In Progress';
        }
        if ($this->progress > 0 || $this->status === 'started') {
            return 'Started';
        }
        return 'Not Started';
    }

    public function getTimelinePhaseKeyAttribute(): string
    {
        $phase = strtolower($this->timeline_phase ?? '');
        if (str_contains($phase, 'phase 1') || str_contains($phase, 'mobilization') || str_contains($phase, 'substructure')) {
            return 'phase1';
        }
        if (str_contains($phase, 'phase 2') || str_contains($phase, 'superstructure') || str_contains($phase, 'framing')) {
            return 'phase2';
        }
        if (str_contains($phase, 'phase 3') || str_contains($phase, 'mep') || str_contains($phase, 'utility') || str_contains($phase, 'infrastructure')) {
            return 'phase3';
        }
        if (str_contains($phase, 'phase 4') || str_contains($phase, 'architectural') || str_contains($phase, 'fit-out') || str_contains($phase, 'finishes')) {
            return 'phase4';
        }
        if (str_contains($phase, 'phase 5') || str_contains($phase, 'commissioning') || str_contains($phase, 'testing') || str_contains($phase, 'handover')) {
            return 'phase5';
        }
        return 'phase1';
    }

    public function getTimelinePhaseBadgeColorAttribute(): string
    {
        return match($this->timeline_phase_key) {
            'phase1' => '#38bdf8', // Cyan
            'phase2' => '#3b82f6', // Blue
            'phase3' => '#f59e0b', // Amber
            'phase4' => '#ec4899', // Pink
            'phase5' => '#10b981', // Emerald
            default => '#94a3b8',
        };
    }

    public function getTimelineWindowLabelAttribute(): string
    {
        if ($this->start_date && $this->due_date) {
            return $this->start_date->format('M d') . ' - ' . $this->due_date->format('M d, Y');
        }
        return $this->timeline_month ?? 'Scheduled Window';
    }
}
