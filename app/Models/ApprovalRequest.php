<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'approvable_type',
        'approvable_id',
        'current_level',
        'status',
        'requested_by',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class);
    }

    public function approvable()
    {
        return $this->morphTo();
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function actions()
    {
        return $this->hasMany(ApprovalAction::class);
    }
}
