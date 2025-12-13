<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'phase_id',
        'photo_path',
        'caption',
        'type',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function phase()
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }
}
