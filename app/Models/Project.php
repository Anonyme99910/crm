<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_number',
        'name',
        'lead_id',
        'quotation_id',
        'manager_id',
        'description',
        'address',
        'status',
        'contract_value',
        'total_cost',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'progress_percentage',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (!$project->project_number) {
                $project->project_number = 'PRJ-' . date('Y') . '-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function phases()
    {
        return $this->hasMany(ProjectPhase::class)->orderBy('sort_order');
    }

    public function photos()
    {
        return $this->hasMany(ProjectPhoto::class);
    }

    public function team()
    {
        return $this->hasMany(ProjectTeam::class);
    }

    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'project_team')->withPivot('role')->withTimestamps();
    }

    public function updates()
    {
        return $this->hasMany(ProjectUpdate::class)->latest();
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function budgets()
    {
        return $this->hasMany(ProjectBudget::class);
    }

    public function materials()
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function qualityInspections()
    {
        return $this->hasMany(QualityInspection::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function calculateProgress()
    {
        $phases = $this->phases;
        if ($phases->isEmpty()) {
            return 0;
        }
        return round($phases->avg('progress_percentage'));
    }

    public function updateProgress()
    {
        $this->update(['progress_percentage' => $this->calculateProgress()]);
    }
}
