<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAmendment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'created_by',
        'amendment_number',
        'title',
        'description',
        'value_change',
        'time_extension_days',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'value_change' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
