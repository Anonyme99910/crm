<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number', 'customer_invoice_id', 'client_id', 'bank_account_id',
        'amount', 'payment_date', 'payment_method', 'reference_number',
        'check_number', 'check_date', 'status', 'notes', 'received_by', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'check_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = 'RCV-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function invoice() { return $this->belongsTo(CustomerInvoice::class, 'customer_invoice_id'); }
    public function client() { return $this->belongsTo(Lead::class, 'client_id'); }
    public function bankAccount() { return $this->belongsTo(BankAccount::class); }
    public function receivedBy() { return $this->belongsTo(User::class, 'received_by'); }
    public function confirmedBy() { return $this->belongsTo(User::class, 'confirmed_by'); }
}
