<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['assignedUser'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->stage, fn($q, $stage) => $q->where('stage', $stage))
            ->when($request->source, fn($q, $source) => $q->where('source', $source))
            ->when($request->assigned_to, fn($q, $userId) => $q->where('assigned_to', $userId))
            ->when($request->search, function($q, $search) {
                $q->where(function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest();

        $leads = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($leads);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'source' => 'required|in:whatsapp,ads,call,website,referral,other',
            'status' => 'nullable|in:hot,warm,cold',
            'stage' => 'nullable|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'project_type' => 'nullable|string|max:255',
            'estimated_budget' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
        ]);

        $lead = Lead::create($request->all());

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'description' => 'تم إنشاء العميل المحتمل',
        ]);

        ActivityLog::log('created', $lead);

        return $this->successResponse($lead->load('assignedUser'), 'تم إضافة العميل بنجاح', 201);
    }

    public function show(Lead $lead)
    {
        $lead->load([
            'assignedUser',
            'activities.user',
            'conversations.user',
            'siteVisits.engineer',
            'quotations',
        ]);

        return $this->successResponse($lead);
    }

    public function update(Request $request, Lead $lead)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'source' => 'sometimes|in:whatsapp,ads,call,website,referral,other',
            'status' => 'nullable|in:hot,warm,cold',
            'stage' => 'nullable|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'project_type' => 'nullable|string|max:255',
            'estimated_budget' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
        ]);

        $oldValues = $lead->toArray();
        $lead->update($request->all());

        if ($request->has('status') && $oldValues['status'] !== $lead->status) {
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'type' => 'status_change',
                'description' => "تم تغيير الحالة من {$oldValues['status']} إلى {$lead->status}",
            ]);
        }

        ActivityLog::log('updated', $lead, $oldValues, $lead->toArray());

        return $this->successResponse($lead->load('assignedUser'), 'تم تحديث بيانات العميل بنجاح');
    }

    public function destroy(Lead $lead)
    {
        ActivityLog::log('deleted', $lead);
        $lead->delete();

        return $this->successResponse(null, 'تم حذف العميل بنجاح');
    }

    public function addActivity(Request $request, Lead $lead)
    {
        $request->validate([
            'type' => 'required|in:call,email,whatsapp,meeting,note,status_change',
            'description' => 'required|string',
            'scheduled_at' => 'nullable|date',
        ]);

        $activity = LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at,
        ]);

        return $this->successResponse($activity->load('user'), 'تم إضافة النشاط بنجاح', 201);
    }

    public function assign(Request $request, Lead $lead)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $lead->update(['assigned_to' => $request->assigned_to]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'description' => 'تم تعيين العميل لموظف جديد',
        ]);

        return $this->successResponse($lead->load('assignedUser'), 'تم تعيين العميل بنجاح');
    }

    public function statistics()
    {
        $stats = [
            'total' => Lead::count(),
            'hot' => Lead::hot()->count(),
            'warm' => Lead::warm()->count(),
            'cold' => Lead::cold()->count(),
            'by_source' => Lead::selectRaw('source, count(*) as count')
                ->groupBy('source')
                ->pluck('count', 'source'),
            'by_stage' => Lead::selectRaw('stage, count(*) as count')
                ->groupBy('stage')
                ->pluck('count', 'stage'),
            'conversion_rate' => Lead::count() > 0 
                ? round((Lead::where('stage', 'won')->count() / Lead::count()) * 100, 2) 
                : 0,
        ];

        return $this->successResponse($stats);
    }
}
