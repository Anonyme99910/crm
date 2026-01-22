<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalRequest;
use App\Models\ApprovalAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalWorkflowController extends Controller
{
    public function workflows(Request $request)
    {
        $query = ApprovalWorkflow::query();

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return $query->orderBy('model_type')->orderBy('min_amount')->get();
    }

    public function storeWorkflow(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model_type' => 'required|string|in:purchase_order,supplier_bill,employee_fund,fund_transfer,customer_invoice,expense',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'approval_levels' => 'required|array|min:1',
            'approval_levels.*.level' => 'required|integer|min:1',
            'approval_levels.*.role' => 'required|string',
            'approval_levels.*.user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $workflow = ApprovalWorkflow::create($validated);
        return response()->json($workflow, 201);
    }

    public function updateWorkflow(Request $request, ApprovalWorkflow $workflow)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'min_amount' => 'sometimes|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'approval_levels' => 'sometimes|array|min:1',
            'is_active' => 'boolean',
        ]);

        $workflow->update($validated);
        return response()->json($workflow);
    }

    public function deleteWorkflow(ApprovalWorkflow $workflow)
    {
        if ($workflow->requests()->whereIn('status', ['pending', 'in_progress'])->exists()) {
            return response()->json(['message' => 'لا يمكن حذف سير عمل له طلبات معلقة'], 422);
        }

        $workflow->delete();
        return response()->json(['message' => 'تم حذف سير العمل']);
    }

    public function pendingApprovals(Request $request)
    {
        $user = auth()->user();
        
        $requests = ApprovalRequest::with(['workflow', 'approvable', 'requestedBy', 'actions'])
            ->where('status', 'pending')
            ->whereHas('workflow', function ($q) use ($user) {
                $q->whereJsonContains('approval_levels', ['user_id' => $user->id])
                  ->orWhereJsonContains('approval_levels', ['role' => $user->role]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $requests;
    }

    public function myRequests(Request $request)
    {
        return ApprovalRequest::with(['workflow', 'approvable', 'actions.user'])
            ->where('requested_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function showRequest(ApprovalRequest $approvalRequest)
    {
        return $approvalRequest->load(['workflow', 'approvable', 'requestedBy', 'actions.user']);
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest)
    {
        if ($approvalRequest->status !== 'pending') {
            return response()->json(['message' => 'الطلب ليس في حالة انتظار'], 422);
        }

        $validated = $request->validate([
            'comments' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $approvalRequest) {
            $user = auth()->user();
            $workflow = $approvalRequest->workflow;
            $currentLevel = $approvalRequest->current_level;

            ApprovalAction::create([
                'approval_request_id' => $approvalRequest->id,
                'user_id' => $user->id,
                'action' => 'approved',
                'level' => $currentLevel,
                'comments' => $validated['comments'] ?? null,
            ]);

            $levels = $workflow->approval_levels;
            $nextLevel = $currentLevel + 1;

            if ($nextLevel > count($levels)) {
                $approvalRequest->update([
                    'status' => 'approved',
                    'completed_at' => now(),
                ]);

                $this->executeApproval($approvalRequest);
            } else {
                $approvalRequest->update(['current_level' => $nextLevel]);
            }

            return response()->json($approvalRequest->fresh(['actions']));
        });
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest)
    {
        if ($approvalRequest->status !== 'pending') {
            return response()->json(['message' => 'الطلب ليس في حالة انتظار'], 422);
        }

        $validated = $request->validate([
            'comments' => 'required|string',
        ]);

        return DB::transaction(function () use ($validated, $approvalRequest) {
            ApprovalAction::create([
                'approval_request_id' => $approvalRequest->id,
                'user_id' => auth()->id(),
                'action' => 'rejected',
                'level' => $approvalRequest->current_level,
                'comments' => $validated['comments'],
            ]);

            $approvalRequest->update([
                'status' => 'rejected',
                'completed_at' => now(),
            ]);

            $this->executeRejection($approvalRequest);

            return response()->json($approvalRequest->fresh(['actions']));
        });
    }

    public function requestApproval(Request $request)
    {
        $validated = $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $workflow = ApprovalWorkflow::getWorkflowFor($validated['model_type'], $validated['amount']);

        if (!$workflow) {
            return response()->json(['message' => 'لا يوجد سير عمل مناسب لهذا الطلب'], 422);
        }

        $existingRequest = ApprovalRequest::where('approvable_type', $validated['model_type'])
            ->where('approvable_id', $validated['model_id'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->first();

        if ($existingRequest) {
            return response()->json(['message' => 'يوجد طلب اعتماد معلق بالفعل'], 422);
        }

        $approvalRequest = ApprovalRequest::create([
            'workflow_id' => $workflow->id,
            'approvable_type' => $validated['model_type'],
            'approvable_id' => $validated['model_id'],
            'requested_by' => auth()->id(),
            'amount' => $validated['amount'],
            'status' => 'pending',
            'current_level' => 1,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json($approvalRequest->load('workflow'), 201);
    }

    protected function executeApproval(ApprovalRequest $request)
    {
        $model = $request->approvable;
        if ($model && method_exists($model, 'onApproved')) {
            $model->onApproved();
        } elseif ($model) {
            $model->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        }
    }

    protected function executeRejection(ApprovalRequest $request)
    {
        $model = $request->approvable;
        if ($model && method_exists($model, 'onRejected')) {
            $model->onRejected();
        } elseif ($model) {
            $model->update(['status' => 'rejected']);
        }
    }

    public function statistics()
    {
        return response()->json([
            'pending_count' => ApprovalRequest::where('status', 'pending')->count(),
            'approved_today' => ApprovalRequest::where('status', 'approved')->whereDate('completed_at', today())->count(),
            'rejected_today' => ApprovalRequest::where('status', 'rejected')->whereDate('completed_at', today())->count(),
            'avg_approval_time' => ApprovalRequest::where('status', 'approved')
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours')
                ->value('avg_hours') ?? 0,
        ]);
    }
}
