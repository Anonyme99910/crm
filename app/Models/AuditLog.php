<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'user_name', 'action', 'model_type', 'model_id', 'model_name',
        'old_values', 'new_values', 'changed_fields', 'ip_address', 'user_agent',
        'url', 'method', 'description', 'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'metadata' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function auditable()
    {
        return $this->morphTo('model');
    }

    public static function log($action, $model = null, $description = null, $oldValues = null, $newValues = null)
    {
        $user = auth()->user();
        $request = request();

        return static::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'model_name' => $model ? ($model->name ?? $model->title ?? $model->invoice_number ?? $model->reference_number ?? null) : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $oldValues && $newValues ? array_keys(array_diff_assoc($newValues, $oldValues)) : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'description' => $description,
        ]);
    }

    public static function logLogin($user, $successful = true, $failureReason = null)
    {
        return static::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? request()->input('email'),
            'action' => $successful ? 'login' : 'login_failed',
            'description' => $successful ? 'تسجيل دخول ناجح' : "فشل تسجيل الدخول: {$failureReason}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]);
    }

    public static function logLogout($user)
    {
        return static::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => 'logout',
            'description' => 'تسجيل خروج',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
