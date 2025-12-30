<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'client',
        'project_date',
        'location',
        'details',
        'order',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'project_date' => 'date',
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }
}
