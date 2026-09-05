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

    public function getTypeBadgeAttribute(): array
    {
        return match ($this->photo_type) {
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
