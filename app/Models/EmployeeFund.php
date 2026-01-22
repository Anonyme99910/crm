<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeFund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'project_id',
        'fund_number',
        'type',
        'requested_amount',
        'approved_amount',
        'spent_amount',
        'returned_amount',
        'balance',
        'purpose',
        'description',
        'status',
        'request_date',
        'expected_settlement_date',
        'actual_settlement_date',
        'approved_by',
        'approved_at',
        'bank_account_id',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'returned_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'request_date' => 'date',
        'expected_settlement_date' => 'date',
        'actual_settlement_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->fund_number = 'FND-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function expenses()
    {
        return $this->hasMany(FundExpense::class, 'employee_fund_id');
    }

    public function updateBalance()
    {
        $this->spent_amount = $this->expenses()->where('status', 'approved')->sum('amount');
        $this->balance = ($this->approved_amount ?? 0) - $this->spent_amount - $this->returned_amount;
        $this->save();
    }
}
