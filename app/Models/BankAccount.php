<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_name',
        'account_number',
        'bank_name',
        'branch',
        'swift_code',
        'iban',
        'account_type',
        'currency',
        'opening_balance',
        'current_balance',
        'minimum_balance',
        'is_active',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'minimum_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(FundTransfer::class, 'from_account_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(FundTransfer::class, 'to_account_id');
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function updateBalance($amount, $type = 'add')
    {
        if ($type === 'add') {
            $this->current_balance += $amount;
        } else {
            $this->current_balance -= $amount;
        }
        $this->save();
    }

    public function isLowBalance()
    {
        return $this->current_balance < $this->minimum_balance;
    }
}
