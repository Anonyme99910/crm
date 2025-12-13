<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'whatsapp',
        'address',
        'source',
        'status',
        'stage',
        'assigned_to',
        'notes',
        'project_type',
        'estimated_budget',
        'expected_close_date',
    ];

    protected $casts = [
        'estimated_budget' => 'decimal:2',
        'expected_close_date' => 'date',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function conversations()
    {
        return $this->hasMany(LeadConversation::class);
    }

    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function callLogs()
    {
        return $this->hasMany(CallLog::class);
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    public function whatsappLogs()
    {
        return $this->hasMany(WhatsappLog::class);
    }

    public function scopeHot($query)
    {
        return $query->where('status', 'hot');
    }

    public function scopeWarm($query)
    {
        return $query->where('status', 'warm');
    }

    public function scopeCold($query)
    {
        return $query->where('status', 'cold');
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeByStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }
}
