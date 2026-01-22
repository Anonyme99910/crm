<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeFund;
use App\Models\FundExpense;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeFundController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeFund::with(['user', 'project', 'approvedBy', 'bankAccount']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $funds = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($funds);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'type' => 'required|in:advance,petty_cash,operation_fund,travel_allowance',
            'requested_amount' => 'required|numeric|min:0.01',
            'purpose' => 'required|string|max:255',
            'description' => 'nullable|string',
            'request_date' => 'required|date',
            'expected_settlement_date' => 'nullable|date|after_or_equal:request_date',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'pending';

        $fund = EmployeeFund::create($validated);

        return response()->json($fund->load(['user', 'project']), 201);
    }

    public function show(EmployeeFund $employeeFund)
    {
        return response()->json($employeeFund->load(['user', 'project', 'approvedBy', 'bankAccount', 'expenses']));
    }

    public function approve(Request $request, EmployeeFund $employeeFund)
    {
        if ($employeeFund->status !== 'pending') {
            return response()->json(['message' => 'Fund request is not pending'], 422);
        }

        $validated = $request->validate([
            'approved_amount' => 'required|numeric|min:0.01|max:' . $employeeFund->requested_amount,
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'notes' => 'nullable|string',
        ]);

        $bankAccount = BankAccount::findOrFail($validated['bank_account_id']);

        if ($bankAccount->current_balance < $validated['approved_amount']) {
            return response()->json(['message' => 'Insufficient balance in selected account'], 422);
        }

        DB::transaction(function () use ($employeeFund, $validated, $bankAccount) {
            $newBalance = $bankAccount->current_balance - $validated['approved_amount'];

            BankTransaction::create([
                'bank_account_id' => $bankAccount->id,
                'type' => 'withdrawal',
                'amount' => $validated['approved_amount'],
                'balance_after' => $newBalance,
                'description' => 'Employee Fund: ' . $employeeFund->purpose . ' - ' . $employeeFund->user->name,
                'transaction_date' => now()->toDateString(),
                'reference_number' => $employeeFund->fund_number,
                'transactionable_type' => EmployeeFund::class,
                'transactionable_id' => $employeeFund->id,
                'created_by' => auth()->id(),
                'status' => 'completed',
            ]);

            $bankAccount->update(['current_balance' => $newBalance]);

            $employeeFund->update([
                'approved_amount' => $validated['approved_amount'],
                'balance' => $validated['approved_amount'],
                'bank_account_id' => $validated['bank_account_id'],
                'status' => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $validated['notes'] ?? $employeeFund->notes,
            ]);
        });

        return response()->json($employeeFund->fresh()->load(['user', 'project', 'approvedBy', 'bankAccount']));
    }

    public function reject(Request $request, EmployeeFund $employeeFund)
    {
        if ($employeeFund->status !== 'pending') {
            return response()->json(['message' => 'Fund request is not pending'], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $employeeFund->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json($employeeFund->fresh());
    }

    public function addExpense(Request $request, EmployeeFund $employeeFund)
    {
        if (!in_array($employeeFund->status, ['active', 'partially_settled'])) {
            return response()->json(['message' => 'Fund is not active'], 422);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'receipt_number' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|string',
            'vendor_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['employee_fund_id'] = $employeeFund->id;
        $validated['status'] = 'pending';

        $expense = FundExpense::create($validated);

        return response()->json($expense, 201);
    }

    public function approveExpense(FundExpense $fundExpense)
    {
        if ($fundExpense->status !== 'pending') {
            return response()->json(['message' => 'Expense is not pending'], 422);
        }

        $fundExpense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $fundExpense->employeeFund->updateBalance();

        return response()->json($fundExpense->fresh());
    }

    public function settle(Request $request, EmployeeFund $employeeFund)
    {
        if (!in_array($employeeFund->status, ['active', 'partially_settled'])) {
            return response()->json(['message' => 'Fund cannot be settled'], 422);
        }

        $validated = $request->validate([
            'returned_amount' => 'required|numeric|min:0',
            'settlement_type' => 'required|in:full,partial,roll_forward',
            'notes' => 'nullable|string',
        ]);

        $employeeFund->updateBalance();

        if ($validated['returned_amount'] > $employeeFund->balance) {
            return response()->json(['message' => 'Return amount exceeds balance'], 422);
        }

        DB::transaction(function () use ($employeeFund, $validated) {
            if ($validated['returned_amount'] > 0) {
                $bankAccount = $employeeFund->bankAccount;
                $newBalance = $bankAccount->current_balance + $validated['returned_amount'];

                BankTransaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'type' => 'deposit',
                    'amount' => $validated['returned_amount'],
                    'balance_after' => $newBalance,
                    'description' => 'Fund Return: ' . $employeeFund->fund_number . ' - ' . $employeeFund->user->name,
                    'transaction_date' => now()->toDateString(),
                    'reference_number' => $employeeFund->fund_number,
                    'transactionable_type' => EmployeeFund::class,
                    'transactionable_id' => $employeeFund->id,
                    'created_by' => auth()->id(),
                    'status' => 'completed',
                ]);

                $bankAccount->update(['current_balance' => $newBalance]);
            }

            $newStatus = $validated['settlement_type'] === 'full' ? 'settled' : 
                        ($validated['settlement_type'] === 'partial' ? 'partially_settled' : 'active');

            $employeeFund->update([
                'returned_amount' => $employeeFund->returned_amount + $validated['returned_amount'],
                'balance' => $employeeFund->balance - $validated['returned_amount'],
                'status' => $newStatus,
                'actual_settlement_date' => $newStatus === 'settled' ? now()->toDateString() : null,
                'notes' => $validated['notes'] ?? $employeeFund->notes,
            ]);
        });

        return response()->json($employeeFund->fresh()->load(['user', 'project', 'approvedBy', 'bankAccount', 'expenses']));
    }

    public function summary()
    {
        $summary = [
            'total_active_funds' => EmployeeFund::whereIn('status', ['active', 'partially_settled'])->sum('balance'),
            'pending_requests' => EmployeeFund::where('status', 'pending')->count(),
            'pending_amount' => EmployeeFund::where('status', 'pending')->sum('requested_amount'),
            'by_type' => EmployeeFund::whereIn('status', ['active', 'partially_settled'])
                ->selectRaw('type, SUM(balance) as total_balance, COUNT(*) as count')
                ->groupBy('type')
                ->get(),
            'overdue_settlements' => EmployeeFund::whereIn('status', ['active', 'partially_settled'])
                ->whereNotNull('expected_settlement_date')
                ->where('expected_settlement_date', '<', now())
                ->count(),
        ];

        return response()->json($summary);
    }
}
