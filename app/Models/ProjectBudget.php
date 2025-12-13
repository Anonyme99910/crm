<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'category',
        'budgeted_amount',
        'actual_amount',
        'notes',
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getVarianceAttribute()
    {
        return $this->budgeted_amount - $this->actual_amount;
    }

    public function getVariancePercentageAttribute()
    {
        if ($this->budgeted_amount == 0) return 0;
        return round(($this->variance / $this->budgeted_amount) * 100, 2);
    }
}
