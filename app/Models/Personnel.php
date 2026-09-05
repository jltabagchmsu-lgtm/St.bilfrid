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
    ];

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
