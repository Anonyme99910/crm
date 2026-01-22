<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = ChartOfAccount::with('parent');

        if ($request->has('type')) {
            $query->where('account_type', $request->type);
        }
        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }
        if ($request->boolean('headers_only')) {
            $query->where('is_header', true);
        }

        $accounts = $query->orderBy('account_code')->get();
        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts',
            'account_name' => 'required|string|max:255',
            'account_name_ar' => 'nullable|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_header' => 'boolean',
            'is_active' => 'boolean',
            'opening_balance' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        if ($validated['parent_id']) {
            $parent = ChartOfAccount::find($validated['parent_id']);
            $validated['level'] = $parent->level + 1;
        } else {
            $validated['level'] = 1;
        }

        $validated['current_balance'] = $validated['opening_balance'] ?? 0;

        $account = ChartOfAccount::create($validated);

        return response()->json($account, 201);
    }

    public function show(ChartOfAccount $chartOfAccount)
    {
        return response()->json($chartOfAccount->load(['parent', 'children']));
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->is_system) {
            return response()->json(['message' => 'Cannot modify system account'], 422);
        }

        $validated = $request->validate([
            'account_name' => 'sometimes|string|max:255',
            'account_name_ar' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_header' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $chartOfAccount->update($validated);

        return response()->json($chartOfAccount);
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->is_system) {
            return response()->json(['message' => 'Cannot delete system account'], 422);
        }

        if ($chartOfAccount->journalLines()->exists()) {
            return response()->json(['message' => 'Cannot delete account with transactions'], 422);
        }

        if ($chartOfAccount->children()->exists()) {
            return response()->json(['message' => 'Cannot delete account with sub-accounts'], 422);
        }

        $chartOfAccount->delete();
        return response()->json(['message' => 'Account deleted successfully']);
    }

    public function tree()
    {
        $accounts = ChartOfAccount::where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $tree = $this->buildTree($accounts);
        return response()->json($tree);
    }

    private function buildTree($accounts, $parentId = null)
    {
        $branch = [];
        foreach ($accounts as $account) {
            if ($account->parent_id == $parentId) {
                $children = $this->buildTree($accounts, $account->id);
                $item = $account->toArray();
                if ($children) {
                    $item['children'] = $children;
                }
                $branch[] = $item;
            }
        }
        return $branch;
    }

    public function trialBalance(Request $request)
    {
        $accounts = ChartOfAccount::where('is_active', true)
            ->where('is_header', false)
            ->where('current_balance', '!=', 0)
            ->orderBy('account_code')
            ->get();

        $totalDebit = 0;
        $totalCredit = 0;

        $data = $accounts->map(function ($account) use (&$totalDebit, &$totalCredit) {
            $debit = $account->normal_balance === 'debit' && $account->current_balance > 0 ? $account->current_balance : 
                    ($account->normal_balance === 'credit' && $account->current_balance < 0 ? abs($account->current_balance) : 0);
            $credit = $account->normal_balance === 'credit' && $account->current_balance > 0 ? $account->current_balance : 
                     ($account->normal_balance === 'debit' && $account->current_balance < 0 ? abs($account->current_balance) : 0);
            
            $totalDebit += $debit;
            $totalCredit += $credit;

            return [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_name_ar' => $account->account_name_ar,
                'debit' => $debit,
                'credit' => $credit,
            ];
        });

        return response()->json([
            'accounts' => $data,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ]);
    }
}
