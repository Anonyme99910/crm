<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'account_code',
        'account_name',
        'account_name_ar',
        'account_type',
        'normal_balance',
        'parent_id',
        'level',
        'is_header',
        'is_active',
        'is_system',
        'opening_balance',
        'current_balance',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_header' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function updateBalance($debit, $credit)
    {
        if ($this->normal_balance === 'debit') {
            $this->current_balance += ($debit - $credit);
        } else {
            $this->current_balance += ($credit - $debit);
        }
        $this->save();
    }

    public static function getByCode($code)
    {
        return static::where('account_code', $code)->first();
    }
}
