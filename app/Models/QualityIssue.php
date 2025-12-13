<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'project_id',
        'reported_by',
        'assigned_to',
        'title',
        'description',
        'severity',
        'status',
        'photos',
        'resolution',
        'resolved_at',
    ];

    protected $casts = [
        'photos' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function inspection()
    {
        return $this->belongsTo(QualityInspection::class, 'inspection_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
