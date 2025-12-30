<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'mediable_id',
        'mediable_type',
        'type',
        'path',
        'thumbnail',
        'title',
        'description',
        'order',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function mediable()
    {
        return $this->morphTo();
    }
}
