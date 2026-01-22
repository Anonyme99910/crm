<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'email', 'ip_address', 'user_agent', 'successful', 'failure_reason', 'user_id',
    ];

    protected $casts = [
        'successful' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public static function record($email, $successful, $failureReason = null, $userId = null)
    {
        return static::create([
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'successful' => $successful,
            'failure_reason' => $failureReason,
            'user_id' => $userId,
        ]);
    }

    public static function recentFailedAttempts($email, $minutes = 15)
    {
        return static::where('email', $email)
            ->where('successful', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    public static function recentFailedAttemptsFromIp($ip, $minutes = 15)
    {
        return static::where('ip_address', $ip)
            ->where('successful', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }
}
