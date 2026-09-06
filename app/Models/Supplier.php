<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'contact_person',
        'email',
        'phone',
        'address',
        'rating',
        'status',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
    ];

    /**
     * Get user accounts associated with this supplier.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get materials offered by this supplier.
     */
    public function materials()
    {
        return $this->hasMany(SupplierMaterial::class);
    }

    /**
     * Get active materials.
     */
    public function activeMaterials()
    {
        return $this->hasMany(SupplierMaterial::class)->where('is_active', true);
    }

    /**
     * Get purchase orders placed for this supplier.
     */
    public function orders()
    {
        return $this->hasMany(SupplierOrder::class);
    }

    /**
     * Get notifications for this supplier.
     */
    public function notifications()
    {
        return $this->hasMany(SupplierNotification::class);
    }

    /**
     * Check if supplier is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Color badge for category.
     */
    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'Windows & Doors' => '#38bdf8',
            'Roofing' => '#ef4444',
            'Structural & Masonry' => '#10b981',
            default => '#818cf8',
        };
    }
}
