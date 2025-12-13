<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'project_type',
        'description',
        'default_items',
        'terms_conditions',
        'is_active',
    ];

    protected $casts = [
        'default_items' => 'array',
        'terms_conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'template_id');
    }
}
