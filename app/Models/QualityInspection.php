<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'phase_id',
        'checklist_id',
        'inspector_id',
        'status',
        'results',
        'notes',
        'score',
        'inspected_at',
    ];

    protected $casts = [
        'results' => 'array',
        'inspected_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function phase()
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    public function checklist()
    {
        return $this->belongsTo(QualityChecklist::class, 'checklist_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function issues()
    {
        return $this->hasMany(QualityIssue::class, 'inspection_id');
    }
}
