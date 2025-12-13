<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'channel',
        'status',
        'budget',
        'spent',
        'start_date',
        'end_date',
        'leads_generated',
        'impressions',
        'clicks',
        'conversion_rate',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getCostPerLeadAttribute()
    {
        if ($this->leads_generated == 0) return 0;
        return round($this->spent / $this->leads_generated, 2);
    }

    public function getClickThroughRateAttribute()
    {
        if ($this->impressions == 0) return 0;
        return round(($this->clicks / $this->impressions) * 100, 2);
    }

    public function getRoiAttribute()
    {
        if ($this->spent == 0) return 0;
        return round((($this->budget - $this->spent) / $this->spent) * 100, 2);
    }
}
