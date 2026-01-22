<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'purchase_order_id',
        'project_id',
        'bill_number',
        'supplier_invoice_number',
        'bill_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
        'matching_status',
        'goods_received',
        'goods_received_date',
        'payment_terms',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'goods_received_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'goods_received' => 'boolean',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->bill_number = 'BILL-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierBillItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function updatePaymentStatus()
    {
        $this->paid_amount = $this->payments()->where('status', 'completed')->sum('amount');
        $this->balance = $this->total_amount - $this->paid_amount;
        
        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        }
        
        $this->save();
    }

    public function performThreeWayMatch()
    {
        if (!$this->purchase_order_id) {
            $this->matching_status = 'unmatched';
            $this->save();
            return;
        }

        $po = $this->purchaseOrder;
        $poTotal = $po->total_amount ?? 0;
        $billTotal = $this->total_amount;

        if (!$this->goods_received) {
            $this->matching_status = 'partial_match';
        } elseif (abs($poTotal - $billTotal) < 0.01) {
            $this->matching_status = 'matched';
        } else {
            $this->matching_status = 'discrepancy';
        }

        $this->save();
    }
}
