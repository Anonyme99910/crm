<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'project_id',
        'contract_id',
        'payment_term_id',
        'created_by',
        'title',
        'description',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'paid_amount',
        'issue_date',
        'due_date',
        'pdf_path',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = 'INV-' . date('Y') . '-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(ContractPaymentTerm::class, 'payment_term_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalanceAttribute()
    {
        return $this->total - $this->paid_amount;
    }

    public function isOverdue()
    {
        return $this->due_date < now() && $this->balance > 0;
    }

    public function updatePaidAmount()
    {
        $paidAmount = $this->payments->sum('amount');
        $status = $paidAmount >= $this->total ? 'paid' : ($paidAmount > 0 ? 'partial' : $this->status);
        
        if ($status !== 'paid' && $this->isOverdue()) {
            $status = 'overdue';
        }

        $this->update([
            'paid_amount' => $paidAmount,
            'status' => $status,
        ]);
    }
}
