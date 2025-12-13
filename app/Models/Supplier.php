<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'email',
        'phone',
        'whatsapp',
        'address',
        'type',
        'specialization',
        'rating',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (!$supplier->code) {
                $prefix = $supplier->type === 'contractor' ? 'CON' : 'SUP';
                $supplier->code = $prefix . '-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function prices()
    {
        return $this->hasMany(SupplierPrice::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function ratings()
    {
        return $this->hasMany(SupplierRating::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function updateRating()
    {
        $ratings = $this->ratings;
        if ($ratings->isEmpty()) {
            return;
        }

        $avgRating = $ratings->avg(function ($r) {
            return ($r->quality_rating + $r->delivery_rating + $r->price_rating + $r->communication_rating) / 4;
        });

        $this->update(['rating' => round($avgRating, 2)]);
    }
}
