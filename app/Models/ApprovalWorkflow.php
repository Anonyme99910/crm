<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'model_type',
        'min_amount',
        'max_amount',
        'approval_levels',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'approval_levels' => 'array',
        'is_active' => 'boolean',
    ];

    public function requests()
    {
        return $this->hasMany(ApprovalRequest::class, 'workflow_id');
    }

    public static function getWorkflowFor($modelType, $amount = null)
    {
        $query = static::where('model_type', $modelType)
            ->where('is_active', true);

        if ($amount !== null) {
            $query->where(function ($q) use ($amount) {
                $q->whereNull('min_amount')->orWhere('min_amount', '<=', $amount);
            })->where(function ($q) use ($amount) {
                $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
            });
        }

        return $query->orderBy('priority', 'desc')->first();
    }
}
