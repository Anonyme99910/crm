<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\SupplierBill;
use App\Models\Project;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    public function profitAndLoss(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $revenues = ChartOfAccount::where('account_type', 'revenue')
            ->where('is_header', false)
            ->get()
            ->map(function ($account) use ($fromDate, $toDate) {
                $amount = JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                        $q->where('status', 'posted')
                          ->whereBetween('entry_date', [$fromDate, $toDate]);
                    })
                    ->selectRaw('SUM(credit) - SUM(debit) as balance')
                    ->value('balance') ?? 0;

                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_name_ar' => $account->account_name_ar,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0);

        $expenses = ChartOfAccount::where('account_type', 'expense')
            ->where('is_header', false)
            ->get()
            ->map(function ($account) use ($fromDate, $toDate) {
                $amount = JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                        $q->where('status', 'posted')
                          ->whereBetween('entry_date', [$fromDate, $toDate]);
                    })
                    ->selectRaw('SUM(debit) - SUM(credit) as balance')
                    ->value('balance') ?? 0;

                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_name_ar' => $account->account_name_ar,
                    'amount' => $amount,
                ];
            })
            ->filter(fn($item) => $item['amount'] != 0);

        $totalRevenue = $revenues->sum('amount');
        $totalExpenses = $expenses->sum('amount');
        $netIncome = $totalRevenue - $totalExpenses;

        return response()->json([
            'period' => ['from' => $fromDate, 'to' => $toDate],
            'revenues' => $revenues->values(),
            'expenses' => $expenses->values(),
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'profit_margin' => $totalRevenue > 0 ? round(($netIncome / $totalRevenue) * 100, 2) : 0,
        ]);
    }

    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $assets = ChartOfAccount::where('account_type', 'asset')
            ->where('is_header', false)
            ->where('is_active', true)
            ->get()
            ->map(function ($account) {
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_name_ar' => $account->account_name_ar,
                    'balance' => $account->current_balance,
                ];
            })
            ->filter(fn($item) => $item['balance'] != 0);

        $liabilities = ChartOfAccount::where('account_type', 'liability')
            ->where('is_header', false)
            ->where('is_active', true)
            ->get()
            ->map(function ($account) {
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_name_ar' => $account->account_name_ar,
                    'balance' => $account->current_balance,
                ];
            })
            ->filter(fn($item) => $item['balance'] != 0);

        $equity = ChartOfAccount::where('account_type', 'equity')
            ->where('is_header', false)
            ->where('is_active', true)
            ->get()
            ->map(function ($account) {
                return [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_name_ar' => $account->account_name_ar,
                    'balance' => $account->current_balance,
                ];
            })
            ->filter(fn($item) => $item['balance'] != 0);

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        return response()->json([
            'as_of_date' => $asOfDate,
            'assets' => $assets->values(),
            'liabilities' => $liabilities->values(),
            'equity' => $equity->values(),
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ]);
    }

    public function cashFlowStatement(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $openingBalance = BankAccount::where('is_active', true)->sum('opening_balance');
        $currentBalance = BankAccount::where('is_active', true)->sum('current_balance');

        $operatingInflows = DB::table('bank_transactions')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->where('status', 'completed')
            ->whereIn('type', ['deposit'])
            ->sum('amount');

        $operatingOutflows = DB::table('bank_transactions')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->where('status', 'completed')
            ->whereIn('type', ['withdrawal'])
            ->sum('amount');

        $transfersIn = DB::table('bank_transactions')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->where('status', 'completed')
            ->where('type', 'transfer_in')
            ->sum('amount');

        $transfersOut = DB::table('bank_transactions')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->where('status', 'completed')
            ->where('type', 'transfer_out')
            ->sum('amount');

        return response()->json([
            'period' => ['from' => $fromDate, 'to' => $toDate],
            'opening_balance' => $openingBalance,
            'operating_activities' => [
                'inflows' => $operatingInflows,
                'outflows' => $operatingOutflows,
                'net' => $operatingInflows - $operatingOutflows,
            ],
            'financing_activities' => [
                'transfers_in' => $transfersIn,
                'transfers_out' => $transfersOut,
                'net' => $transfersIn - $transfersOut,
            ],
            'net_change' => ($operatingInflows - $operatingOutflows),
            'closing_balance' => $currentBalance,
        ]);
    }

    public function projectProfitability(Request $request)
    {
        $projectId = $request->input('project_id');

        $query = Project::with(['invoices', 'expenses', 'payments']);

        if ($projectId) {
            $query->where('id', $projectId);
        }

        $projects = $query->get()->map(function ($project) {
            $totalRevenue = $project->invoices->sum('total_amount');
            $totalCollected = $project->payments->where('status', 'completed')->sum('amount');
            $totalExpenses = $project->expenses->sum('amount');
            $budgetedCost = $project->budget_amount ?? 0;

            return [
                'id' => $project->id,
                'name' => $project->name,
                'client' => $project->client_name ?? 'N/A',
                'status' => $project->status,
                'contract_value' => $project->contract_value ?? 0,
                'budgeted_cost' => $budgetedCost,
                'total_revenue' => $totalRevenue,
                'total_collected' => $totalCollected,
                'outstanding' => $totalRevenue - $totalCollected,
                'total_expenses' => $totalExpenses,
                'gross_profit' => $totalRevenue - $totalExpenses,
                'profit_margin' => $totalRevenue > 0 ? round((($totalRevenue - $totalExpenses) / $totalRevenue) * 100, 2) : 0,
                'budget_variance' => $budgetedCost - $totalExpenses,
                'budget_utilization' => $budgetedCost > 0 ? round(($totalExpenses / $budgetedCost) * 100, 2) : 0,
            ];
        });

        $summary = [
            'total_projects' => $projects->count(),
            'total_revenue' => $projects->sum('total_revenue'),
            'total_expenses' => $projects->sum('total_expenses'),
            'total_profit' => $projects->sum('gross_profit'),
            'average_margin' => $projects->avg('profit_margin'),
            'profitable_projects' => $projects->where('gross_profit', '>', 0)->count(),
            'loss_making_projects' => $projects->where('gross_profit', '<', 0)->count(),
        ];

        return response()->json([
            'projects' => $projects,
            'summary' => $summary,
        ]);
    }

    public function executiveDashboard()
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $startOfYear = now()->startOfYear()->toDateString();

        // Cash Position
        $cashPosition = BankAccount::where('is_active', true)
            ->selectRaw('SUM(current_balance) as total, currency')
            ->groupBy('currency')
            ->get();

        // Receivables
        $totalReceivables = Invoice::whereIn('status', ['sent', 'partially_paid'])
            ->sum(DB::raw('total_amount - paid_amount'));

        $overdueReceivables = Invoice::whereIn('status', ['sent', 'partially_paid'])
            ->where('due_date', '<', $today)
            ->sum(DB::raw('total_amount - paid_amount'));

        // Payables
        $totalPayables = SupplierBill::whereIn('status', ['approved', 'partially_paid'])
            ->sum('balance');

        $overduePayables = SupplierBill::whereIn('status', ['approved', 'partially_paid'])
            ->where('due_date', '<', $today)
            ->sum('balance');

        // Monthly Revenue
        $monthlyRevenue = Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$startOfMonth, $today])
            ->sum('amount');

        // YTD Revenue
        $ytdRevenue = Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$startOfYear, $today])
            ->sum('amount');

        // Active Projects
        $activeProjects = Project::whereIn('status', ['in_progress', 'on_hold'])->count();

        // Pending Approvals
        $pendingApprovals = [
            'funds' => DB::table('employee_funds')->where('status', 'pending')->count(),
            'bills' => SupplierBill::where('status', 'pending_approval')->count(),
            'transfers' => DB::table('fund_transfers')->where('status', 'pending')->count(),
        ];

        return response()->json([
            'cash_position' => $cashPosition,
            'receivables' => [
                'total' => $totalReceivables,
                'overdue' => $overdueReceivables,
            ],
            'payables' => [
                'total' => $totalPayables,
                'overdue' => $overduePayables,
            ],
            'revenue' => [
                'monthly' => $monthlyRevenue,
                'ytd' => $ytdRevenue,
            ],
            'active_projects' => $activeProjects,
            'pending_approvals' => $pendingApprovals,
            'net_working_capital' => $cashPosition->sum('total') + $totalReceivables - $totalPayables,
        ]);
    }
}
