<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\ProjectPhoto;
use App\Models\ProjectUpdate;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['lead', 'manager'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->manager_id, fn($q, $id) => $q->where('manager_id', $id))
            ->when($request->search, function($q, $search) {
                $q->where(function($query) use ($search) {
                    $query->where('project_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->latest();

        $projects = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($projects);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'lead_id' => 'required|exists:leads,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'manager_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'contract_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'expected_end_date' => 'required|date|after:start_date',
        ]);

        $project = Project::create($request->all());

        $defaultPhases = [
            ['name' => 'التجهيزات الأولية', 'sort_order' => 1],
            ['name' => 'أعمال المحارة', 'sort_order' => 2],
            ['name' => 'أعمال الكهرباء', 'sort_order' => 3],
            ['name' => 'أعمال السباكة', 'sort_order' => 4],
            ['name' => 'أعمال النجارة', 'sort_order' => 5],
            ['name' => 'أعمال الدهانات', 'sort_order' => 6],
            ['name' => 'التشطيبات النهائية', 'sort_order' => 7],
        ];

        foreach ($defaultPhases as $phase) {
            ProjectPhase::create([
                'project_id' => $project->id,
                'name' => $phase['name'],
                'sort_order' => $phase['sort_order'],
            ]);
        }

        return $this->successResponse($project->load(['lead', 'manager', 'phases']), 'تم إنشاء المشروع بنجاح', 201);
    }

    public function show(Project $project)
    {
        $project->load([
            'lead',
            'manager',
            'quotation',
            'phases.tasks',
            'photos',
            'teamMembers',
            'updates' => fn($q) => $q->limit(10),
            'contract',
            'invoices',
        ]);

        return $this->successResponse($project);
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'manager_id' => 'sometimes|exists:users,id',
            'description' => 'nullable|string',
            'address' => 'sometimes|string',
            'status' => 'sometimes|in:pending,in_progress,on_hold,completed,cancelled',
            'contract_value' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date',
            'expected_end_date' => 'sometimes|date',
            'actual_end_date' => 'nullable|date',
        ]);

        if ($request->status === 'completed' && !$project->actual_end_date) {
            $request->merge(['actual_end_date' => now()]);
        }

        $project->update($request->all());

        return $this->successResponse($project->load(['lead', 'manager']), 'تم تحديث المشروع بنجاح');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return $this->successResponse(null, 'تم حذف المشروع بنجاح');
    }

    public function updatePhase(Request $request, Project $project, ProjectPhase $phase)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed,on_hold',
            'progress_percentage' => 'sometimes|integer|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
        ]);

        $phase->update($request->all());

        return $this->successResponse($phase, 'تم تحديث المرحلة بنجاح');
    }

    public function addPhase(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $phase = ProjectPhase::create([
            'project_id' => $project->id,
            'name' => $request->name,
            'description' => $request->description,
            'budget' => $request->budget ?? 0,
            'sort_order' => $project->phases()->count() + 1,
        ]);

        return $this->successResponse($phase, 'تم إضافة المرحلة بنجاح', 201);
    }

    public function uploadPhotos(Request $request, Project $project)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|max:5120',
            'phase_id' => 'nullable|exists:project_phases,id',
            'type' => 'required|in:before,during,after',
            'caption' => 'nullable|string',
        ]);

        $uploadedPhotos = [];

        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('projects/' . $project->id, 'public');
            
            $uploadedPhotos[] = ProjectPhoto::create([
                'project_id' => $project->id,
                'phase_id' => $request->phase_id,
                'photo_path' => $path,
                'caption' => $request->caption,
                'type' => $request->type,
                'taken_at' => now(),
            ]);
        }

        return $this->successResponse($uploadedPhotos, 'تم رفع الصور بنجاح', 201);
    }

    public function addUpdate(Request $request, Project $project)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visible_to_client' => 'boolean',
        ]);

        $update = ProjectUpdate::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $request->content,
            'visible_to_client' => $request->visible_to_client ?? false,
        ]);

        return $this->successResponse($update->load('user'), 'تم إضافة التحديث بنجاح', 201);
    }

    public function addTeamMember(Request $request, Project $project)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|string|max:255',
        ]);

        $project->teamMembers()->syncWithoutDetaching([
            $request->user_id => ['role' => $request->role]
        ]);

        return $this->successResponse($project->teamMembers, 'تم إضافة عضو الفريق بنجاح');
    }

    public function removeTeamMember(Project $project, $userId)
    {
        $project->teamMembers()->detach($userId);

        return $this->successResponse(null, 'تم إزالة عضو الفريق بنجاح');
    }

    public function timeline(Project $project)
    {
        $timeline = collect();

        $timeline = $timeline->merge(
            $project->phases->map(fn($p) => [
                'type' => 'phase',
                'data' => $p,
                'date' => $p->start_date ?? $p->created_at,
            ])
        );

        $timeline = $timeline->merge(
            $project->updates->map(fn($u) => [
                'type' => 'update',
                'data' => $u,
                'date' => $u->created_at,
            ])
        );

        $timeline = $timeline->sortByDesc('date')->values();

        return $this->successResponse($timeline);
    }

    public function statistics()
    {
        $stats = [
            'total' => Project::count(),
            'in_progress' => Project::where('status', 'in_progress')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
            'total_value' => Project::sum('contract_value'),
            'average_progress' => round(Project::where('status', 'in_progress')->avg('progress_percentage') ?? 0),
            'delayed' => Project::where('status', 'in_progress')
                ->where('expected_end_date', '<', now())
                ->count(),
        ];

        return $this->successResponse($stats);
    }
}
