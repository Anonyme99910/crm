<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FundTransfer;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FundTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = FundTransfer::with(['fromAccount', 'toAccount', 'requestedBy', 'approvedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('from_date')) {
            $query->where('transfer_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('transfer_date', '<=', $request->to_date);
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($transfers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:bank_accounts,id',
            'to_account_id' => 'required|exists:bank_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'fee' => 'nullable|numeric|min:0',
            'transfer_date' => 'required|date',
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $fromAccount = BankAccount::findOrFail($validated['from_account_id']);
        
        $totalAmount = $validated['amount'] + ($validated['fee'] ?? 0);
        if ($fromAccount->current_balance < $totalAmount) {
            return response()->json(['message' => 'Insufficient balance in source account'], 422);
        }

        $validated['requested_by'] = auth()->id();
        $validated['status'] = 'pending';

        $transfer = FundTransfer::create($validated);

        return response()->json($transfer->load(['fromAccount', 'toAccount', 'requestedBy']), 201);
    }

    public function show(FundTransfer $fundTransfer)
    {
        return response()->json($fundTransfer->load(['fromAccount', 'toAccount', 'requestedBy', 'approvedBy']));
    }

    public function approve(FundTransfer $fundTransfer)
    {
        if ($fundTransfer->status !== 'pending') {
            return response()->json(['message' => 'Transfer is not pending'], 422);
        }

        $fromAccount = $fundTransfer->fromAccount;
        $toAccount = $fundTransfer->toAccount;
        $totalAmount = $fundTransfer->amount + $fundTransfer->fee;

        if ($fromAccount->current_balance < $totalAmount) {
            return response()->json(['message' => 'Insufficient balance in source account'], 422);
        }

        DB::transaction(function () use ($fundTransfer, $fromAccount, $toAccount, $totalAmount) {
            // Deduct from source account
            $fromNewBalance = $fromAccount->current_balance - $totalAmount;
            BankTransaction::create([
                'bank_account_id' => $fromAccount->id,
                'type' => 'transfer_out',
                'amount' => $totalAmount,
                'balance_after' => $fromNewBalance,
                'description' => 'Transfer to ' . $toAccount->account_name . ' - ' . $fundTransfer->purpose,
                'transaction_date' => $fundTransfer->transfer_date,
                'reference_number' => $fundTransfer->reference_number,
                'transactionable_type' => FundTransfer::class,
                'transactionable_id' => $fundTransfer->id,
                'created_by' => auth()->id(),
                'status' => 'completed',
            ]);
            $fromAccount->update(['current_balance' => $fromNewBalance]);

            // Add to destination account
            $toNewBalance = $toAccount->current_balance + $fundTransfer->amount;
            BankTransaction::create([
                'bank_account_id' => $toAccount->id,
                'type' => 'transfer_in',
                'amount' => $fundTransfer->amount,
                'balance_after' => $toNewBalance,
                'description' => 'Transfer from ' . $fromAccount->account_name . ' - ' . $fundTransfer->purpose,
                'transaction_date' => $fundTransfer->transfer_date,
                'reference_number' => $fundTransfer->reference_number,
                'transactionable_type' => FundTransfer::class,
                'transactionable_id' => $fundTransfer->id,
                'created_by' => auth()->id(),
                'status' => 'completed',
            ]);
            $toAccount->update(['current_balance' => $toNewBalance]);

            // Update transfer status
            $fundTransfer->update([
                'status' => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return response()->json($fundTransfer->fresh()->load(['fromAccount', 'toAccount', 'requestedBy', 'approvedBy']));
    }

    public function reject(Request $request, FundTransfer $fundTransfer)
    {
        if ($fundTransfer->status !== 'pending') {
            return response()->json(['message' => 'Transfer is not pending'], 422);
        }

        $fundTransfer->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $request->input('reason', $fundTransfer->notes),
        ]);

        return response()->json($fundTransfer->fresh());
    }
}
