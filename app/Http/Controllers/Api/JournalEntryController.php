<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with(['createdBy', 'postedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('from_date')) {
            $query->where('entry_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('entry_date', '<=', $request->to_date);
        }

        $entries = $query->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($entries);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:255',
            'type' => 'required|in:manual,adjustment',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.project_id' => 'nullable|exists:projects,id',
            'lines.*.cost_center' => 'nullable|string|max:50',
        ]);

        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return response()->json(['message' => 'Journal entry must be balanced. Debit: ' . $totalDebit . ', Credit: ' . $totalCredit], 422);
        }

        $entry = DB::transaction(function () use ($validated, $totalDebit, $totalCredit) {
            $entry = JournalEntry::create([
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'notes' => $validated['notes'] ?? null,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'created_by' => auth()->id(),
                'status' => 'draft',
            ]);

            foreach ($validated['lines'] as $line) {
                if ($line['debit'] > 0 || $line['credit'] > 0) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $line['account_id'],
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                        'description' => $line['description'] ?? null,
                        'project_id' => $line['project_id'] ?? null,
                        'cost_center' => $line['cost_center'] ?? null,
                    ]);
                }
            }

            return $entry;
        });

        return response()->json($entry->load(['lines.account', 'createdBy']), 201);
    }

    public function show(JournalEntry $journalEntry)
    {
        return response()->json($journalEntry->load(['lines.account', 'lines.project', 'createdBy', 'postedBy']));
    }

    public function update(Request $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'draft') {
            return response()->json(['message' => 'Only draft entries can be modified'], 422);
        }

        $validated = $request->validate([
            'entry_date' => 'sometimes|date',
            'description' => 'sometimes|string|max:255',
            'notes' => 'nullable|string',
            'lines' => 'sometimes|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.project_id' => 'nullable|exists:projects,id',
            'lines.*.cost_center' => 'nullable|string|max:50',
        ]);

        if (isset($validated['lines'])) {
            $totalDebit = collect($validated['lines'])->sum('debit');
            $totalCredit = collect($validated['lines'])->sum('credit');

            if (abs($totalDebit - $totalCredit) > 0.01) {
                return response()->json(['message' => 'Journal entry must be balanced'], 422);
            }
        }

        DB::transaction(function () use ($journalEntry, $validated) {
            $journalEntry->update([
                'entry_date' => $validated['entry_date'] ?? $journalEntry->entry_date,
                'description' => $validated['description'] ?? $journalEntry->description,
                'notes' => $validated['notes'] ?? $journalEntry->notes,
            ]);

            if (isset($validated['lines'])) {
                $journalEntry->lines()->delete();

                $totalDebit = 0;
                $totalCredit = 0;

                foreach ($validated['lines'] as $line) {
                    if ($line['debit'] > 0 || $line['credit'] > 0) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $line['account_id'],
                            'debit' => $line['debit'],
                            'credit' => $line['credit'],
                            'description' => $line['description'] ?? null,
                            'project_id' => $line['project_id'] ?? null,
                            'cost_center' => $line['cost_center'] ?? null,
                        ]);
                        $totalDebit += $line['debit'];
                        $totalCredit += $line['credit'];
                    }
                }

                $journalEntry->update([
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                ]);
            }
        });

        return response()->json($journalEntry->fresh()->load(['lines.account', 'createdBy']));
    }

    public function post(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'draft') {
            return response()->json(['message' => 'Only draft entries can be posted'], 422);
        }

        if (!$journalEntry->isBalanced()) {
            return response()->json(['message' => 'Cannot post unbalanced entry'], 422);
        }

        DB::transaction(function () use ($journalEntry) {
            foreach ($journalEntry->lines as $line) {
                $line->account->updateBalance($line->debit, $line->credit);
            }

            $journalEntry->update([
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);
        });

        return response()->json($journalEntry->fresh()->load(['lines.account', 'createdBy', 'postedBy']));
    }

    public function reverse(Request $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'posted') {
            return response()->json(['message' => 'Only posted entries can be reversed'], 422);
        }

        $validated = $request->validate([
            'reversal_date' => 'required|date|after_or_equal:' . $journalEntry->entry_date,
            'reason' => 'required|string|max:255',
        ]);

        $reversalEntry = DB::transaction(function () use ($journalEntry, $validated) {
            // Reverse account balances
            foreach ($journalEntry->lines as $line) {
                $line->account->updateBalance($line->credit, $line->debit);
            }

            // Create reversal entry
            $reversalEntry = JournalEntry::create([
                'entry_date' => $validated['reversal_date'],
                'description' => 'Reversal of ' . $journalEntry->entry_number . ': ' . $validated['reason'],
                'type' => 'adjustment',
                'total_debit' => $journalEntry->total_credit,
                'total_credit' => $journalEntry->total_debit,
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
                'status' => 'posted',
                'reversal_of' => $journalEntry->id,
            ]);

            foreach ($journalEntry->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversalEntry->id,
                    'account_id' => $line->account_id,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'description' => 'Reversal: ' . ($line->description ?? ''),
                    'project_id' => $line->project_id,
                    'cost_center' => $line->cost_center,
                ]);
            }

            $journalEntry->update([
                'status' => 'reversed',
                'reversed_by' => auth()->id(),
                'reversed_at' => now(),
            ]);

            return $reversalEntry;
        });

        return response()->json($reversalEntry->load(['lines.account', 'createdBy']));
    }

    public function destroy(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'draft') {
            return response()->json(['message' => 'Only draft entries can be deleted'], 422);
        }

        $journalEntry->lines()->delete();
        $journalEntry->delete();

        return response()->json(['message' => 'Journal entry deleted successfully']);
    }
}
