<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->role, fn($q, $role) => $q->where('role', $role))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->is_active))
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,sales,engineer,accountant,customer',
        ]);

        $user = User::create([
            ...$request->except('password'),
            'password' => Hash::make($request->password),
        ]);

        return $this->successResponse($user, 'تم إنشاء المستخدم بنجاح', 201);
    }

    public function show(User $user)
    {
        return $this->successResponse($user);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'sometimes|in:admin,manager,sales,engineer,accountant,customer',
            'is_active' => 'boolean',
        ]);

        $user->update($request->all());

        return $this->successResponse($user, 'تم تحديث المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return $this->successResponse(null, 'تم حذف المستخدم بنجاح');
    }

    public function notifications(Request $request)
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->when($request->unread, fn($q) => $q->unread())
            ->latest()
            ->paginate($request->per_page ?? 20);

        return $this->paginatedResponse($notifications);
    }

    public function markNotificationRead(Notification $notification)
    {
        $notification->markAsRead();
        return $this->successResponse(null, 'تم تحديث الإشعار');
    }

    public function markAllNotificationsRead()
    {
        Notification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
        return $this->successResponse(null, 'تم تحديث جميع الإشعارات');
    }

    public function salesTeam()
    {
        $users = User::whereIn('role', ['sales', 'manager'])->where('is_active', true)->get();
        return $this->successResponse($users);
    }

    public function engineers()
    {
        $users = User::where('role', 'engineer')->where('is_active', true)->get();
        return $this->successResponse($users);
    }
}
