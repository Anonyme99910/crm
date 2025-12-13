<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'user_id',
        'project_id',
        'date',
        'check_in',
        'check_out',
        'hours_worked',
        'overtime_hours',
        'status',
        'notes',
        'latitude_in',
        'longitude_in',
        'latitude_out',
        'longitude_out',
    ];

    protected $casts = [
        'date' => 'date',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'latitude_in' => 'decimal:8',
        'longitude_in' => 'decimal:8',
        'latitude_out' => 'decimal:8',
        'longitude_out' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function calculateHoursWorked()
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        $checkIn = \Carbon\Carbon::parse($this->check_in);
        $checkOut = \Carbon\Carbon::parse($this->check_out);
        
        return round($checkOut->diffInMinutes($checkIn) / 60, 2);
    }
}
