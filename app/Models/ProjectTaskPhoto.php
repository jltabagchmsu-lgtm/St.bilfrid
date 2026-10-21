<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskPhoto extends Model
{
    use HasFactory;

    protected $table = 'project_task_photos';

    protected $fillable = [
        'project_task_id',
        'photo_path',
        'caption',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->photo_path, 'http://') || str_starts_with($this->photo_path, 'https://')) {
            return $this->photo_path;
        }
        return asset($this->photo_path);
    }
}
