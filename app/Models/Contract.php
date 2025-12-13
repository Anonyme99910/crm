<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'project_id',
        'quotation_id',
        'created_by',
        'title',
        'description',
        'status',
        'total_value',
        'start_date',
        'end_date',
        'terms_conditions',
        'scope_of_work',
        'pdf_path',
        'client_signed_at',
        'client_signature',
        'company_signed_at',
        'company_signature',
    ];

    protected $casts = [
        'total_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'client_signed_at' => 'datetime',
        'company_signed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {
            if (!$contract->contract_number) {
                $contract->contract_number = 'CTR-' . date('Y') . '-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentTerms()
    {
        return $this->hasMany(ContractPaymentTerm::class)->orderBy('sort_order');
    }

    public function amendments()
    {
        return $this->hasMany(ContractAmendment::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function isSigned()
    {
        return $this->client_signed_at && $this->company_signed_at;
    }
}
