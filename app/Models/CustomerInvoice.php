<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'invoice_type', 'project_id', 'contract_id', 'client_id',
        'invoice_date', 'due_date', 'subtotal', 'tax_amount', 'retention_amount',
        'discount_amount', 'total_amount', 'paid_amount', 'balance', 'completion_percentage',
        'previous_billing', 'current_billing', 'status', 'description', 'terms', 'notes',
        'created_by', 'approved_by', 'approved_at', 'sent_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'previous_billing' => 'decimal:2',
        'current_billing' => 'decimal:2',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $prefix = match($invoice->invoice_type) {
                    'progress' => 'PRG',
                    'final' => 'FNL',
                    'retention' => 'RET',
                    'variation' => 'VAR',
                    'advance' => 'ADV',
                    default => 'INV',
                };
                $invoice->invoice_number = $prefix . '-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function contract() { return $this->belongsTo(Contract::class); }
    public function client() { return $this->belongsTo(Lead::class, 'client_id'); }
    public function items() { return $this->hasMany(CustomerInvoiceItem::class); }
    public function payments() { return $this->hasMany(CustomerPayment::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }

    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('total_amount');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount - $this->retention_amount;
        $this->balance = $this->total_amount - $this->paid_amount;
        $this->save();
    }

    public function updatePaymentStatus()
    {
        $this->paid_amount = $this->payments()->where('status', 'confirmed')->sum('amount');
        $this->balance = $this->total_amount - $this->paid_amount;
        
        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date < now() && $this->balance > 0) {
            $this->status = 'overdue';
        }
        $this->save();
    }

    public function isOverdue()
    {
        return $this->due_date < now() && $this->balance > 0;
    }
}
