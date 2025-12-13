<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'status',
        'sort_order',
        'start_date',
        'end_date',
        'progress_percentage',
        'budget',
        'actual_cost',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($phase) {
            $phase->project->updateProgress();
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function photos()
    {
        return $this->hasMany(ProjectPhoto::class, 'phase_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'phase_id');
    }

    public function materials()
    {
        return $this->hasMany(ProjectMaterial::class, 'phase_id');
    }

    public function qualityInspections()
    {
        return $this->hasMany(QualityInspection::class, 'phase_id');
    }
}
