<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(MaterialCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MaterialCategory::class, 'parent_id');
    }

    public function materials()
    {
        return $this->hasMany(Material::class, 'category_id');
    }
}
