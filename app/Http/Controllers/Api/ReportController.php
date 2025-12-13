<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dashboard()
    {
        return $this->successResponse([
            'leads' => [
                'total' => Lead::count(),
                'this_month' => Lead::whereMonth('created_at', now()->month)->count(),
                'hot' => Lead::hot()->count(),
            ],
            'projects' => [
                'total' => Project::count(),
                'in_progress' => Project::where('status', 'in_progress')->count(),
                'completed' => Project::where('status', 'completed')->count(),
            ],
            'finance' => [
                'total_revenue' => Payment::sum('amount'),
                'pending_invoices' => Invoice::whereIn('status', ['sent', 'partial'])->sum('total'),
                'this_month_revenue' => Payment::whereMonth('payment_date', now()->month)->sum('amount'),
            ],
            'quotations' => [
                'total' => Quotation::count(),
                'pending' => Quotation::whereIn('status', ['draft', 'sent'])->count(),
                'accepted' => Quotation::where('status', 'accepted')->count(),
            ],
        ]);
    }

    public function leadsReport(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth();
        $dateTo = $request->date_to ?? now()->endOfMonth();

        return $this->successResponse([
            'total' => Lead::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'by_source' => Lead::whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('source, count(*) as count')
                ->groupBy('source')
                ->pluck('count', 'source'),
            'by_status' => Lead::whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'conversion_rate' => $this->calculateConversionRate($dateFrom, $dateTo),
        ]);
    }

    public function projectsReport(Request $request)
    {
        return $this->successResponse([
            'by_status' => Project::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'total_value' => Project::sum('contract_value'),
            'average_value' => Project::avg('contract_value'),
            'delayed' => Project::where('status', 'in_progress')
                ->where('expected_end_date', '<', now())
                ->count(),
            'average_progress' => Project::where('status', 'in_progress')->avg('progress_percentage'),
        ]);
    }

    public function financialReport(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfYear();
        $dateTo = $request->date_to ?? now()->endOfYear();

        $income = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->sum('amount');
        $expenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->where('status', 'approved')
            ->sum('amount');

        return $this->successResponse([
            'income' => $income,
            'expenses' => $expenses,
            'profit' => $income - $expenses,
            'monthly_income' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
                ->selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
                ->groupBy('month')
                ->pluck('total', 'month'),
            'expenses_by_category' => Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
                ->where('status', 'approved')
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->pluck('total', 'category'),
        ]);
    }

    public function salesPerformance(Request $request)
    {
        return $this->successResponse([
            'by_user' => Lead::with('assignedUser')
                ->selectRaw('assigned_to, count(*) as leads_count')
                ->groupBy('assigned_to')
                ->get()
                ->map(fn($l) => [
                    'user' => $l->assignedUser?->name,
                    'leads' => $l->leads_count,
                    'won' => Lead::where('assigned_to', $l->assigned_to)->where('stage', 'won')->count(),
                ]),
        ]);
    }

    private function calculateConversionRate($from, $to)
    {
        $total = Lead::whereBetween('created_at', [$from, $to])->count();
        if ($total === 0) return 0;
        $won = Lead::whereBetween('created_at', [$from, $to])->where('stage', 'won')->count();
        return round(($won / $total) * 100, 2);
    }
}
