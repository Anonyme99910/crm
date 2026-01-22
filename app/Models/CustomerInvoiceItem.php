<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_invoice_id', 'description', 'unit', 'quantity', 'unit_price',
        'previous_quantity', 'current_quantity', 'tax_rate', 'tax_amount', 'total_amount', 'boq_item_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'previous_quantity' => 'decimal:4',
        'current_quantity' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function invoice() { return $this->belongsTo(CustomerInvoice::class, 'customer_invoice_id'); }

    public function calculateTotals()
    {
        $subtotal = $this->quantity * $this->unit_price;
        $this->tax_amount = $subtotal * ($this->tax_rate / 100);
        $this->total_amount = $subtotal + $this->tax_amount;
        return $this;
    }
}
