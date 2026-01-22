<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_fund_id',
        'expense_number',
        'amount',
        'category',
        'description',
        'expense_date',
        'receipt_number',
        'receipt_image',
        'vendor_name',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->expense_number = 'EXP-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function employeeFund()
    {
        return $this->belongsTo(EmployeeFund::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
