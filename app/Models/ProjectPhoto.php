<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'photo_type',
        'plan_discipline',
        'title',
        'description',
        'file_path',
        'is_primary',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'date',
        'is_primary' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getIsBlueprintAttribute(): bool
    {
        return str_starts_with($this->photo_type, 'blueprint')
            || in_array($this->photo_type, ['civil', 'plumbing', 'electrical', 'architectural', 'other'])
            || !empty($this->plan_discipline);
    }

    public function getEffectiveDisciplineAttribute(): ?string
    {
        if (!empty($this->plan_discipline)) {
            return strtolower($this->plan_discipline);
        }

        if (str_starts_with($this->photo_type, 'blueprint_')) {
            return str_replace('blueprint_', '', $this->photo_type);
        }

        if (in_array($this->photo_type, ['civil', 'plumbing', 'electrical', 'architectural', 'other'])) {
            return $this->photo_type;
        }

        return null;
    }

    public function getTypeBadgeAttribute(): array
    {
        return match ($this->photo_type) {
            'blueprint_architectural', 'architectural' => [
                'label' => 'Architectural CAD Blueprint',
                'color' => '#3b82f6',
                'bg' => 'rgba(59, 130, 246, 0.15)',
                'border' => 'rgba(59, 130, 246, 0.3)',
                'icon' => '🏛️',
            ],
            'blueprint_civil', 'civil' => [
                'label' => 'Civil & Structural Blueprint',
                'color' => '#f97316',
                'bg' => 'rgba(249, 115, 22, 0.15)',
                'border' => 'rgba(249, 115, 22, 0.3)',
                'icon' => '🏗️',
            ],
            'blueprint_electrical', 'electrical' => [
                'label' => 'Electrical CAD Plan',
                'color' => '#eab308',
                'bg' => 'rgba(234, 179, 8, 0.15)',
                'border' => 'rgba(234, 179, 8, 0.3)',
                'icon' => '⚡',
            ],
            'blueprint_plumbing', 'plumbing' => [
                'label' => 'Plumbing & Sanitary Blueprint',
                'color' => '#06b6d4',
                'bg' => 'rgba(6, 182, 212, 0.15)',
                'border' => 'rgba(6, 182, 212, 0.3)',
                'icon' => '🚰',
            ],
            'blueprint_other', 'other' => [
                'label' => 'Other Related Technical Plan',
                'color' => '#8b5cf6',
                'bg' => 'rgba(139, 92, 246, 0.15)',
                'border' => 'rgba(139, 92, 246, 0.3)',
                'icon' => '📐',
            ],
            'blueprint' => [
                'label' => 'Technical Blueprint / CAD',
                'color' => '#38bdf8',
                'bg' => 'rgba(56, 189, 248, 0.15)',
                'border' => 'rgba(56, 189, 248, 0.3)',
                'icon' => '📐',
            ],
            '3d_render' => [
                'label' => '3D Architectural Render (Target Design)',
                'color' => '#ec4899',
                'bg' => 'rgba(236, 72, 153, 0.15)',
                'border' => 'rgba(236, 72, 153, 0.3)',
                'icon' => '🎨',
            ],
            'client_want' => [
                'label' => 'Client Design Inspiration',
                'color' => '#a855f7',
                'bg' => 'rgba(168, 85, 247, 0.15)',
                'border' => 'rgba(168, 85, 247, 0.3)',
                'icon' => '💡',
            ],
            'structural' => [
                'label' => 'Structural & Foundation Works',
                'color' => '#f59e0b',
                'bg' => 'rgba(245, 158, 11, 0.15)',
                'border' => 'rgba(245, 158, 11, 0.3)',
                'icon' => '🏗️',
            ],
            'finishing' => [
                'label' => 'Architectural & Turnkey Finishes',
                'color' => '#10b981',
                'bg' => 'rgba(16, 185, 129, 0.15)',
                'border' => 'rgba(16, 185, 129, 0.3)',
                'icon' => '✨',
            ],
            default => [
                'label' => 'Actual On-Site Progress',
                'color' => '#ef4444',
                'bg' => 'rgba(239, 68, 68, 0.15)',
                'border' => 'rgba(239, 68, 68, 0.3)',
                'icon' => '📸',
            ],
        };
    }
}
