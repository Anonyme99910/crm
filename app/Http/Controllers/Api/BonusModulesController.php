<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\QualityChecklist;
use App\Models\QualityInspection;
use App\Models\QualityIssue;
use App\Models\MaintenanceRequest;
use App\Models\MarketingCampaign;
use Illuminate\Http\Request;

class BonusModulesController extends Controller
{
    // HR - Attendance
    public function checkIn(Request $request)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $attendance = Attendance::updateOrCreate(
            ['user_id' => auth()->id(), 'date' => today()],
            [
                'check_in' => now()->format('H:i:s'),
                'project_id' => $request->project_id,
                'latitude_in' => $request->latitude,
                'longitude_in' => $request->longitude,
                'status' => now()->hour > 9 ? 'late' : 'present',
            ]
        );

        return $this->successResponse($attendance, 'تم تسجيل الحضور بنجاح');
    }

    public function checkOut(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', today())
            ->firstOrFail();

        $attendance->update([
            'check_out' => now()->format('H:i:s'),
            'latitude_out' => $request->latitude,
            'longitude_out' => $request->longitude,
            'hours_worked' => $attendance->calculateHoursWorked(),
        ]);

        return $this->successResponse($attendance, 'تم تسجيل الانصراف بنجاح');
    }

    public function attendanceReport(Request $request)
    {
        $attendance = Attendance::with('user')
            ->when($request->user_id, fn($q, $id) => $q->where('user_id', $id))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('date', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('date', '<=', $d))
            ->latest('date')
            ->paginate($request->per_page ?? 20);

        return $this->paginatedResponse($attendance);
    }

    // Quality Control
    public function qualityChecklists()
    {
        return $this->successResponse(QualityChecklist::where('is_active', true)->get());
    }

    public function createInspection(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'checklist_id' => 'required|exists:quality_checklists,id',
        ]);

        $inspection = QualityInspection::create([
            ...$request->all(),
            'inspector_id' => auth()->id(),
        ]);

        return $this->successResponse($inspection, 'تم إنشاء الفحص بنجاح', 201);
    }

    public function submitInspection(Request $request, QualityInspection $inspection)
    {
        $request->validate([
            'results' => 'required|array',
            'status' => 'required|in:passed,failed,conditional',
            'score' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $inspection->update([
            ...$request->all(),
            'inspected_at' => now(),
        ]);

        return $this->successResponse($inspection, 'تم تسجيل نتائج الفحص بنجاح');
    }

    public function reportIssue(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
        ]);

        $issue = QualityIssue::create([
            ...$request->all(),
            'reported_by' => auth()->id(),
        ]);

        return $this->successResponse($issue, 'تم الإبلاغ عن المشكلة بنجاح', 201);
    }

    // Maintenance
    public function maintenanceRequests(Request $request)
    {
        $requests = MaintenanceRequest::with(['project', 'assignee'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->project_id, fn($q, $id) => $q->where('project_id', $id))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($requests);
    }

    public function createMaintenanceRequest(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'nullable|string',
            'client_name' => 'nullable|string',
            'client_phone' => 'nullable|string',
        ]);

        $maintenance = MaintenanceRequest::create([
            ...$request->all(),
            'reported_by' => auth()->id(),
        ]);

        return $this->successResponse($maintenance, 'تم إنشاء طلب الصيانة بنجاح', 201);
    }

    public function updateMaintenanceRequest(Request $request, MaintenanceRequest $maintenance)
    {
        $request->validate([
            'status' => 'sometimes|in:new,assigned,in_progress,completed,closed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'resolution' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ]);

        if ($request->status === 'completed') {
            $request->merge(['completed_at' => now()]);
        }

        $maintenance->update($request->all());

        return $this->successResponse($maintenance, 'تم تحديث طلب الصيانة بنجاح');
    }

    // Marketing
    public function marketingCampaigns(Request $request)
    {
        $campaigns = MarketingCampaign::query()
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($campaigns);
    }

    public function createCampaign(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:facebook,google,instagram,tiktok,other',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $campaign = MarketingCampaign::create($request->all());

        return $this->successResponse($campaign, 'تم إنشاء الحملة بنجاح', 201);
    }

    public function marketingDashboard()
    {
        return $this->successResponse([
            'active_campaigns' => MarketingCampaign::where('status', 'active')->count(),
            'total_budget' => MarketingCampaign::sum('budget'),
            'total_spent' => MarketingCampaign::sum('spent'),
            'total_leads' => MarketingCampaign::sum('leads_generated'),
            'avg_cost_per_lead' => MarketingCampaign::where('leads_generated', '>', 0)
                ->selectRaw('AVG(spent / leads_generated) as cpl')
                ->value('cpl') ?? 0,
        ]);
    }
}
