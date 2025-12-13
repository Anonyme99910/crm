<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'engineer_id',
        'created_by',
        'scheduled_at',
        'completed_at',
        'status',
        'address',
        'latitude',
        'longitude',
        'notes',
        'client_requirements',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos()
    {
        return $this->hasMany(SiteVisitPhoto::class);
    }

    public function measurements()
    {
        return $this->hasMany(SiteVisitMeasurement::class);
    }

    public function report()
    {
        return $this->hasOne(SiteVisitReport::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
