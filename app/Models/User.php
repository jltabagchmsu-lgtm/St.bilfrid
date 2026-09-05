<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user is Master Admin.
     */
    public function isAdmin(): bool
    {
        return empty($this->role) || $this->role === 'admin';
    }

    /**
     * Check if user is Roofing Transfer Officer.
     */
    public function isRoofingOfficer(): bool
    {
        return $this->role === 'roofing_transfer';
    }

    /**
     * Check if user is Windows & Doors Transfer Officer.
     */
    public function isWindowsDoorsOfficer(): bool
    {
        return $this->role === 'windows_doors_transfer';
    }

    /**
     * Get user role display title.
     */
    public function getRoleTitleAttribute(): string
    {
        return match ($this->role) {
            'roofing_transfer' => 'Roofing Transfer Officer',
            'windows_doors_transfer' => 'Windows & Doors Transfer Officer',
            default => 'Master Administrator',
        };
    }

    /**
     * Get user default landing portal route.
     */
    public function getPortalRouteAttribute(): string
    {
        return match ($this->role) {
            'roofing_transfer' => route('roofing.index'),
            'windows_doors_transfer' => route('windowsDoors.index'),
            default => route('dashboard'),
        };
    }
}
