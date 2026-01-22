<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::orderBy('account_name')->get();
        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:bank_accounts',
            'bank_name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'account_type' => 'required|in:checking,savings,cash,petty_cash',
            'currency' => 'required|string|size:3',
            'opening_balance' => 'required|numeric|min:0',
            'minimum_balance' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['current_balance'] = $validated['opening_balance'];

        if ($validated['is_default'] ?? false) {
            BankAccount::where('is_default', true)->update(['is_default' => false]);
        }

        $account = BankAccount::create($validated);

        return response()->json($account, 201);
    }

    public function show(BankAccount $bankAccount)
    {
        $bankAccount->load(['transactions' => function ($query) {
            $query->orderBy('transaction_date', 'desc')->limit(50);
        }]);
        return response()->json($bankAccount);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'account_name' => 'sometimes|string|max:255',
            'account_number' => 'sometimes|string|unique:bank_accounts,account_number,' . $bankAccount->id,
            'bank_name' => 'sometimes|string|max:255',
            'branch' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'account_type' => 'sometimes|in:checking,savings,cash,petty_cash',
            'currency' => 'sometimes|string|size:3',
            'minimum_balance' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if (($validated['is_default'] ?? false) && !$bankAccount->is_default) {
            BankAccount::where('is_default', true)->update(['is_default' => false]);
        }

        $bankAccount->update($validated);

        return response()->json($bankAccount);
    }

    public function destroy(BankAccount $bankAccount)
    {
        if ($bankAccount->transactions()->exists()) {
            return response()->json(['message' => 'Cannot delete account with transactions'], 422);
        }

        $bankAccount->delete();
        return response()->json(['message' => 'Account deleted successfully']);
    }

    public function transactions(BankAccount $bankAccount, Request $request)
    {
        $query = $bankAccount->transactions()->with(['createdBy', 'approvedBy']);

        if ($request->has('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($transactions);
    }

    public function deposit(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $newBalance = $bankAccount->current_balance + $validated['amount'];

        $transaction = BankTransaction::create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'deposit',
            'amount' => $validated['amount'],
            'balance_after' => $newBalance,
            'description' => $validated['description'],
            'transaction_date' => $validated['transaction_date'],
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'completed',
        ]);

        $bankAccount->update(['current_balance' => $newBalance]);

        return response()->json($transaction, 201);
    }

    public function withdraw(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($bankAccount->current_balance < $validated['amount']) {
            return response()->json(['message' => 'Insufficient balance'], 422);
        }

        $newBalance = $bankAccount->current_balance - $validated['amount'];

        $transaction = BankTransaction::create([
            'bank_account_id' => $bankAccount->id,
            'type' => 'withdrawal',
            'amount' => $validated['amount'],
            'balance_after' => $newBalance,
            'description' => $validated['description'],
            'transaction_date' => $validated['transaction_date'],
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'completed',
        ]);

        $bankAccount->update(['current_balance' => $newBalance]);

        return response()->json($transaction, 201);
    }

    public function summary()
    {
        $accounts = BankAccount::where('is_active', true)->get();
        
        $summary = [
            'total_balance' => $accounts->sum('current_balance'),
            'accounts_count' => $accounts->count(),
            'low_balance_accounts' => $accounts->filter(fn($a) => $a->isLowBalance())->count(),
            'by_type' => $accounts->groupBy('account_type')->map(fn($group) => [
                'count' => $group->count(),
                'balance' => $group->sum('current_balance'),
            ]),
            'by_currency' => $accounts->groupBy('currency')->map(fn($group) => $group->sum('current_balance')),
        ];

        return response()->json($summary);
    }
}
