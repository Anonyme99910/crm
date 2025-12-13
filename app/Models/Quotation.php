<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_number',
        'lead_id',
        'site_visit_id',
        'template_id',
        'created_by',
        'title',
        'description',
        'status',
        'subtotal',
        'discount_amount',
        'discount_type',
        'tax_rate',
        'tax_amount',
        'total',
        'cost_price',
        'profit_margin',
        'valid_until',
        'terms_conditions',
        'notes',
        'pdf_path',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'view_token',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($quotation) {
            if (!$quotation->quotation_number) {
                $quotation->quotation_number = 'QT-' . date('Y') . '-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
            }
            if (!$quotation->view_token) {
                $quotation->view_token = Str::random(32);
            }
        });
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function siteVisit()
    {
        return $this->belongsTo(SiteVisit::class);
    }

    public function template()
    {
        return $this->belongsTo(QuotationTemplate::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function calculateTotals()
    {
        $subtotal = $this->items->sum('total');
        $costPrice = $this->items->sum(function ($item) {
            return $item->cost_price * $item->quantity;
        });

        $discountAmount = $this->discount_type === 'percentage' 
            ? ($subtotal * $this->discount_amount / 100) 
            : $this->discount_amount;

        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * ($this->tax_rate / 100);
        $total = $taxableAmount + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'cost_price' => $costPrice,
            'profit_margin' => $total - $costPrice,
        ]);
    }
}
