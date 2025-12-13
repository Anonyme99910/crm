<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'category',
        'name',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'cost_price',
        'total',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total = $item->quantity * $item->unit_price;
        });

        static::saved(function ($item) {
            $item->quotation->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->quotation->calculateTotals();
        });
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
