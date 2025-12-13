<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'project_id',
        'reported_by',
        'assigned_to',
        'client_name',
        'client_phone',
        'title',
        'description',
        'priority',
        'status',
        'category',
        'photos',
        'resolution',
        'scheduled_at',
        'completed_at',
        'is_warranty',
        'cost',
    ];

    protected $casts = [
        'photos' => 'array',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_warranty' => 'boolean',
        'cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (!$request->ticket_number) {
                $request->ticket_number = 'MNT-' . date('Y') . '-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
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
