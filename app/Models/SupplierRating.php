<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'project_id',
        'user_id',
        'quality_rating',
        'delivery_rating',
        'price_rating',
        'communication_rating',
        'comments',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($rating) {
            $rating->supplier->updateRating();
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAverageRatingAttribute()
    {
        return ($this->quality_rating + $this->delivery_rating + $this->price_rating + $this->communication_rating) / 4;
    }
}
