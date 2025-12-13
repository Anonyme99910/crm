<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskComment;
use App\Models\Notification;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['project', 'assignee', 'phase'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->priority, fn($q, $priority) => $q->where('priority', $priority))
            ->when($request->project_id, fn($q, $id) => $q->where('project_id', $id))
            ->when($request->assigned_to, fn($q, $id) => $q->where('assigned_to', $id))
            ->when($request->overdue, fn($q) => $q->overdue())
            ->when($request->due_date, fn($q, $date) => $q->whereDate('due_date', $date))
            ->latest();

        $tasks = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($tasks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'assigned_to' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'checklists' => 'nullable|array',
            'checklists.*' => 'string',
        ]);

        $task = Task::create([
            ...$request->except('checklists'),
            'created_by' => auth()->id(),
        ]);

        if ($request->checklists) {
            foreach ($request->checklists as $index => $item) {
                TaskChecklist::create([
                    'task_id' => $task->id,
                    'item' => $item,
                    'sort_order' => $index,
                ]);
            }
        }

        if ($task->assigned_to) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'type' => 'task_assigned',
                'title' => 'مهمة جديدة',
                'message' => "تم تعيين مهمة جديدة لك: {$task->title}",
                'notifiable_type' => Task::class,
                'notifiable_id' => $task->id,
            ]);
        }

        return $this->successResponse($task->load(['project', 'assignee', 'checklists']), 'تم إنشاء المهمة بنجاح', 201);
    }

    public function show(Task $task)
    {
        $task->load(['project', 'phase', 'assignee', 'creator', 'checklists', 'comments.user']);

        return $this->successResponse($task);
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'assigned_to' => 'nullable|exists:users,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled,on_hold',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'actual_hours' => 'nullable|integer|min:0',
        ]);

        $oldAssignee = $task->assigned_to;

        if ($request->status === 'in_progress' && !$task->started_at) {
            $request->merge(['started_at' => now()]);
        }

        if ($request->status === 'completed' && !$task->completed_at) {
            $request->merge(['completed_at' => now()]);
        }

        $task->update($request->all());

        if ($request->assigned_to && $request->assigned_to != $oldAssignee) {
            Notification::create([
                'user_id' => $request->assigned_to,
                'type' => 'task_assigned',
                'title' => 'مهمة جديدة',
                'message' => "تم تعيين مهمة جديدة لك: {$task->title}",
                'notifiable_type' => Task::class,
                'notifiable_id' => $task->id,
            ]);
        }

        return $this->successResponse($task->load(['project', 'assignee']), 'تم تحديث المهمة بنجاح');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return $this->successResponse(null, 'تم حذف المهمة بنجاح');
    }

    public function addChecklist(Request $request, Task $task)
    {
        $request->validate([
            'item' => 'required|string|max:255',
        ]);

        $checklist = TaskChecklist::create([
            'task_id' => $task->id,
            'item' => $request->item,
            'sort_order' => $task->checklists()->count(),
        ]);

        return $this->successResponse($checklist, 'تم إضافة البند بنجاح', 201);
    }

    public function toggleChecklist(TaskChecklist $checklist)
    {
        if ($checklist->is_completed) {
            $checklist->markIncomplete();
        } else {
            $checklist->markComplete();
        }

        return $this->successResponse($checklist, 'تم تحديث البند بنجاح');
    }

    public function deleteChecklist(TaskChecklist $checklist)
    {
        $checklist->delete();

        return $this->successResponse(null, 'تم حذف البند بنجاح');
    }

    public function addComment(Request $request, Task $task)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return $this->successResponse($comment->load('user'), 'تم إضافة التعليق بنجاح', 201);
    }

    public function myTasks(Request $request)
    {
        $tasks = Task::with(['project', 'phase'])
            ->where('assigned_to', auth()->id())
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->today, fn($q) => $q->whereDate('due_date', today()))
            ->orderBy('due_date')
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->get();

        return $this->successResponse($tasks);
    }

    public function todayTasks()
    {
        $tasks = Task::with(['project', 'assignee'])
            ->whereDate('due_date', today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->get();

        return $this->successResponse($tasks);
    }

    public function overdueTasks()
    {
        $tasks = Task::with(['project', 'assignee'])
            ->overdue()
            ->orderBy('due_date')
            ->get();

        return $this->successResponse($tasks);
    }

    public function statistics()
    {
        $stats = [
            'total' => Task::count(),
            'pending' => Task::pending()->count(),
            'in_progress' => Task::inProgress()->count(),
            'completed' => Task::where('status', 'completed')->count(),
            'overdue' => Task::overdue()->count(),
            'by_priority' => Task::selectRaw('priority, count(*) as count')
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->groupBy('priority')
                ->pluck('count', 'priority'),
        ];

        return $this->successResponse($stats);
    }
}
