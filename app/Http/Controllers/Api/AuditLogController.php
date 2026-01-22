<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('model_name', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(50);
    }

    public function show(AuditLog $auditLog)
    {
        return $auditLog->load('user');
    }

    public function modelHistory(Request $request)
    {
        $validated = $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        return AuditLog::with('user')
            ->where('model_type', 'like', '%' . $validated['model_type'] . '%')
            ->where('model_id', $validated['model_id'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function userActivity(Request $request, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        return AuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
    }

    public function loginAttempts(Request $request)
    {
        $query = LoginAttempt::with('user');

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('successful')) {
            $query->where('successful', $request->boolean('successful'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return $query->orderBy('created_at', 'desc')->paginate(50);
    }

    public function securityDashboard()
    {
        $today = now()->startOfDay();
        $lastWeek = now()->subDays(7);

        return response()->json([
            'login_stats' => [
                'successful_today' => LoginAttempt::where('successful', true)->where('created_at', '>=', $today)->count(),
                'failed_today' => LoginAttempt::where('successful', false)->where('created_at', '>=', $today)->count(),
                'successful_week' => LoginAttempt::where('successful', true)->where('created_at', '>=', $lastWeek)->count(),
                'failed_week' => LoginAttempt::where('successful', false)->where('created_at', '>=', $lastWeek)->count(),
            ],
            'activity_stats' => [
                'total_actions_today' => AuditLog::where('created_at', '>=', $today)->count(),
                'creates_today' => AuditLog::where('action', 'create')->where('created_at', '>=', $today)->count(),
                'updates_today' => AuditLog::where('action', 'update')->where('created_at', '>=', $today)->count(),
                'deletes_today' => AuditLog::where('action', 'delete')->where('created_at', '>=', $today)->count(),
            ],
            'top_users_today' => AuditLog::where('created_at', '>=', $today)
                ->selectRaw('user_id, user_name, COUNT(*) as actions_count')
                ->groupBy('user_id', 'user_name')
                ->orderByDesc('actions_count')
                ->limit(10)
                ->get(),
            'suspicious_activity' => [
                'multiple_failed_logins' => LoginAttempt::where('successful', false)
                    ->where('created_at', '>=', $lastWeek)
                    ->selectRaw('email, ip_address, COUNT(*) as attempts')
                    ->groupBy('email', 'ip_address')
                    ->having('attempts', '>=', 5)
                    ->get(),
            ],
            'recent_logins' => LoginAttempt::with('user')
                ->where('successful', true)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ]);
    }

    public function exportLogs(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'format' => 'in:csv,json',
        ]);

        $logs = AuditLog::with('user')
            ->whereBetween('created_at', [$validated['from_date'], $validated['to_date'] . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        AuditLog::log('export', null, 'تصدير سجلات المراجعة');

        if ($validated['format'] === 'csv') {
            $headers = ['ID', 'User', 'Action', 'Model', 'Description', 'IP', 'Date'];
            $rows = $logs->map(fn($log) => [
                $log->id,
                $log->user_name,
                $log->action,
                $log->model_type . '#' . $log->model_id,
                $log->description,
                $log->ip_address,
                $log->created_at->format('Y-m-d H:i:s'),
            ]);

            return response()->json(['headers' => $headers, 'rows' => $rows]);
        }

        return response()->json($logs);
    }

    public function actions()
    {
        return AuditLog::distinct()
            ->pluck('action')
            ->filter()
            ->values();
    }
}
