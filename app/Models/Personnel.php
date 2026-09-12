<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    use HasFactory;

    protected $table = 'personnel';

    protected $fillable = [
        'name',
        'title',
        'email',
        'phone',
        'license_no',
        'specialization',
        'license_expiry_date',
        'license_status',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
    ];

    /**
     * Check if the personnel license is expired or inactive.
     */
    public function isLicenseExpired(): bool
    {
        if ($this->license_status && in_array(strtolower($this->license_status), ['expired', 'inactive', 'suspended', 'revoked'])) {
            return true;
        }

        if ($this->license_expiry_date) {
            return \Carbon\Carbon::parse($this->license_expiry_date)->startOfDay()->isPast();
        }

        return false;
    }

    /**
     * Get a visual badge representation of the license status.
     */
    public function getLicenseStatusBadgeAttribute(): array
    {
        if ($this->isLicenseExpired()) {
            return [
                'status' => 'expired',
                'label' => 'EXPIRED LICENSE',
                'class' => 'badge-danger',
                'bg' => '#ef4444',
                'color' => '#ffffff',
            ];
        }

        return [
            'status' => 'active',
            'label' => 'ACTIVE',
            'class' => 'badge-success',
            'bg' => '#10b981',
            'color' => '#ffffff',
        ];
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_personnel')
            ->withPivot('assignment_role')
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'assigned_personnel_id');
    }
}
