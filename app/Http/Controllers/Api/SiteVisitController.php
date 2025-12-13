<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteVisit;
use App\Models\SiteVisitPhoto;
use App\Models\SiteVisitMeasurement;
use App\Models\SiteVisitReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteVisitController extends Controller
{
    public function index(Request $request)
    {
        $query = SiteVisit::with(['lead', 'engineer'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->engineer_id, fn($q, $id) => $q->where('engineer_id', $id))
            ->when($request->lead_id, fn($q, $id) => $q->where('lead_id', $id))
            ->when($request->date_from, fn($q, $date) => $q->whereDate('scheduled_at', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->whereDate('scheduled_at', '<=', $date))
            ->latest('scheduled_at');

        $visits = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($visits);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'engineer_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'client_requirements' => 'nullable|string',
        ]);

        $visit = SiteVisit::create([
            ...$request->all(),
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($visit->load(['lead', 'engineer']), 'تم جدولة المعاينة بنجاح', 201);
    }

    public function show(SiteVisit $siteVisit)
    {
        $siteVisit->load([
            'lead',
            'engineer',
            'creator',
            'photos',
            'measurements',
            'report',
        ]);

        return $this->successResponse($siteVisit);
    }

    public function update(Request $request, SiteVisit $siteVisit)
    {
        $request->validate([
            'engineer_id' => 'sometimes|exists:users,id',
            'scheduled_at' => 'sometimes|date',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled,rescheduled',
            'address' => 'sometimes|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'client_requirements' => 'nullable|string',
        ]);

        if ($request->status === 'completed' && !$siteVisit->completed_at) {
            $request->merge(['completed_at' => now()]);
        }

        $siteVisit->update($request->all());

        return $this->successResponse($siteVisit->load(['lead', 'engineer']), 'تم تحديث المعاينة بنجاح');
    }

    public function destroy(SiteVisit $siteVisit)
    {
        $siteVisit->delete();

        return $this->successResponse(null, 'تم حذف المعاينة بنجاح');
    }

    public function uploadPhotos(Request $request, SiteVisit $siteVisit)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|max:5120',
            'category' => 'nullable|in:exterior,interior,measurements,existing_condition,other',
        ]);

        $uploadedPhotos = [];

        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('site-visits/' . $siteVisit->id, 'public');
            
            $uploadedPhotos[] = SiteVisitPhoto::create([
                'site_visit_id' => $siteVisit->id,
                'photo_path' => $path,
                'category' => $request->category ?? 'other',
            ]);
        }

        return $this->successResponse($uploadedPhotos, 'تم رفع الصور بنجاح', 201);
    }

    public function addMeasurement(Request $request, SiteVisit $siteVisit)
    {
        $request->validate([
            'room_name' => 'required|string|max:255',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'custom_measurements' => 'nullable|array',
        ]);

        $area = null;
        if ($request->length && $request->width) {
            $area = $request->length * $request->width;
        }

        $measurement = SiteVisitMeasurement::create([
            'site_visit_id' => $siteVisit->id,
            'room_name' => $request->room_name,
            'length' => $request->length,
            'width' => $request->width,
            'height' => $request->height,
            'area' => $area,
            'notes' => $request->notes,
            'custom_measurements' => $request->custom_measurements,
        ]);

        return $this->successResponse($measurement, 'تم إضافة القياسات بنجاح', 201);
    }

    public function createReport(Request $request, SiteVisit $siteVisit)
    {
        $request->validate([
            'summary' => 'required|string',
            'recommendations' => 'nullable|string',
            'scope_of_work' => 'nullable|array',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        $reportNumber = 'RPT-' . date('Y') . '-' . str_pad(SiteVisitReport::count() + 1, 5, '0', STR_PAD_LEFT);

        $report = SiteVisitReport::updateOrCreate(
            ['site_visit_id' => $siteVisit->id],
            [
                'report_number' => $reportNumber,
                'summary' => $request->summary,
                'recommendations' => $request->recommendations,
                'scope_of_work' => $request->scope_of_work,
                'estimated_cost' => $request->estimated_cost,
            ]
        );

        return $this->successResponse($report, 'تم إنشاء التقرير بنجاح', 201);
    }

    public function todayVisits()
    {
        $visits = SiteVisit::with(['lead', 'engineer'])
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();

        return $this->successResponse($visits);
    }

    public function engineerSchedule(Request $request, $engineerId)
    {
        $visits = SiteVisit::with(['lead'])
            ->where('engineer_id', $engineerId)
            ->when($request->date, 
                fn($q, $date) => $q->whereDate('scheduled_at', $date),
                fn($q) => $q->whereDate('scheduled_at', '>=', today())
            )
            ->orderBy('scheduled_at')
            ->get();

        return $this->successResponse($visits);
    }
}
