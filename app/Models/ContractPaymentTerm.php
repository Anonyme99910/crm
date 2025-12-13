<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractPaymentTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'description',
        'percentage',
        'amount',
        'due_date',
        'milestone',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'payment_term_id');
    }
}
