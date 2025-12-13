<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\ProjectUpdate;
use Illuminate\Http\Request;

class CustomerPortalController extends Controller
{
    public function myProjects(Request $request)
    {
        $user = $request->user();
        
        $projects = Project::with(['manager', 'phases'])
            ->whereHas('lead', fn($q) => $q->where('email', $user->email))
            ->get();

        return $this->successResponse($projects);
    }

    public function projectDetails($projectId)
    {
        $project = Project::with([
            'manager',
            'phases',
            'photos' => fn($q) => $q->latest()->limit(20),
            'updates' => fn($q) => $q->where('visible_to_client', true)->latest(),
        ])->findOrFail($projectId);

        return $this->successResponse($project);
    }

    public function projectPhotos($projectId)
    {
        $project = Project::findOrFail($projectId);
        $photos = $project->photos()->latest()->get();

        return $this->successResponse($photos);
    }

    public function projectUpdates($projectId)
    {
        $updates = ProjectUpdate::where('project_id', $projectId)
            ->where('visible_to_client', true)
            ->with('user')
            ->latest()
            ->get();

        return $this->successResponse($updates);
    }

    public function myInvoices(Request $request)
    {
        $user = $request->user();
        
        $invoices = Invoice::with('project')
            ->whereHas('project.lead', fn($q) => $q->where('email', $user->email))
            ->latest()
            ->get();

        return $this->successResponse($invoices);
    }

    public function pendingPayments(Request $request)
    {
        $user = $request->user();
        
        $invoices = Invoice::with('project')
            ->whereHas('project.lead', fn($q) => $q->where('email', $user->email))
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->get();

        $total = $invoices->sum(fn($i) => $i->total - $i->paid_amount);

        return $this->successResponse([
            'invoices' => $invoices,
            'total_pending' => $total,
        ]);
    }

    public function sendMessage(Request $request, $projectId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $project = Project::findOrFail($projectId);

        ProjectUpdate::create([
            'project_id' => $projectId,
            'user_id' => auth()->id(),
            'title' => 'رسالة من العميل',
            'content' => $request->message,
            'visible_to_client' => true,
        ]);

        return $this->successResponse(null, 'تم إرسال الرسالة بنجاح');
    }
}
