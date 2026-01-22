<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceItem;
use App\Models\CustomerPayment;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerInvoice::with(['project', 'client', 'createdBy']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        return $query->orderBy('invoice_date', 'desc')->paginate(20);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_type' => 'required|in:progress,final,retention,variation,advance',
            'project_id' => 'required|exists:projects,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'client_id' => 'required|exists:leads,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'completion_percentage' => 'nullable|numeric|min:0|max:100',
            'retention_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.previous_quantity' => 'nullable|numeric|min:0',
            'items.*.current_quantity' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $invoice = CustomerInvoice::create([
                ...$validated,
                'created_by' => auth()->id(),
                'retention_amount' => $validated['retention_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
            ]);

            foreach ($validated['items'] as $itemData) {
                $item = new CustomerInvoiceItem($itemData);
                $item->customer_invoice_id = $invoice->id;
                $item->tax_rate = $itemData['tax_rate'] ?? 15;
                $item->calculateTotals();
                $item->save();
            }

            $invoice->calculateTotals();
            return response()->json($invoice->load('items'), 201);
        });
    }

    public function show(CustomerInvoice $customerInvoice)
    {
        return $customerInvoice->load(['project', 'contract', 'client', 'items', 'payments', 'createdBy', 'approvedBy']);
    }

    public function update(Request $request, CustomerInvoice $customerInvoice)
    {
        if (!in_array($customerInvoice->status, ['draft'])) {
            return response()->json(['message' => 'لا يمكن تعديل الفاتورة في هذه الحالة'], 422);
        }

        $validated = $request->validate([
            'invoice_date' => 'sometimes|date',
            'due_date' => 'sometimes|date',
            'completion_percentage' => 'nullable|numeric|min:0|max:100',
            'retention_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
        ]);

        return DB::transaction(function () use ($validated, $customerInvoice) {
            $customerInvoice->update($validated);

            if (isset($validated['items'])) {
                $customerInvoice->items()->delete();
                foreach ($validated['items'] as $itemData) {
                    $item = new CustomerInvoiceItem($itemData);
                    $item->customer_invoice_id = $customerInvoice->id;
                    $item->tax_rate = $itemData['tax_rate'] ?? 15;
                    $item->calculateTotals();
                    $item->save();
                }
            }

            $customerInvoice->calculateTotals();
            return response()->json($customerInvoice->load('items'));
        });
    }

    public function approve(CustomerInvoice $customerInvoice)
    {
        if ($customerInvoice->status !== 'draft') {
            return response()->json(['message' => 'الفاتورة ليست في حالة مسودة'], 422);
        }

        $customerInvoice->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json($customerInvoice);
    }

    public function send(CustomerInvoice $customerInvoice)
    {
        if (!in_array($customerInvoice->status, ['approved', 'sent'])) {
            return response()->json(['message' => 'الفاتورة يجب أن تكون معتمدة أولاً'], 422);
        }

        $customerInvoice->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return response()->json($customerInvoice);
    }

    public function recordPayment(Request $request, CustomerInvoice $customerInvoice)
    {
        if (!in_array($customerInvoice->status, ['sent', 'partially_paid', 'overdue'])) {
            return response()->json(['message' => 'لا يمكن تسجيل دفعة لهذه الفاتورة'], 422);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $customerInvoice->balance,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,check,cash,credit_card',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'reference_number' => 'nullable|string',
            'check_number' => 'nullable|string',
            'check_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $customerInvoice) {
            $payment = CustomerPayment::create([
                ...$validated,
                'customer_invoice_id' => $customerInvoice->id,
                'client_id' => $customerInvoice->client_id,
                'received_by' => auth()->id(),
                'status' => 'pending',
            ]);

            return response()->json($payment, 201);
        });
    }

    public function confirmPayment(CustomerPayment $payment)
    {
        if ($payment->status !== 'pending') {
            return response()->json(['message' => 'الدفعة ليست في حالة معلقة'], 422);
        }

        return DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            if ($payment->bank_account_id) {
                $bankAccount = BankAccount::find($payment->bank_account_id);
                $bankAccount->deposit($payment->amount, 'customer_payment', "تحصيل فاتورة {$payment->invoice->invoice_number}", auth()->id());
            }

            $payment->invoice->updatePaymentStatus();
            return response()->json($payment);
        });
    }

    public function agingReport(Request $request)
    {
        $invoices = CustomerInvoice::with(['client', 'project'])
            ->where('balance', '>', 0)
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->get();

        $aging = [
            'current' => ['count' => 0, 'amount' => 0, 'invoices' => []],
            '1_30' => ['count' => 0, 'amount' => 0, 'invoices' => []],
            '31_60' => ['count' => 0, 'amount' => 0, 'invoices' => []],
            '61_90' => ['count' => 0, 'amount' => 0, 'invoices' => []],
            'over_90' => ['count' => 0, 'amount' => 0, 'invoices' => []],
        ];

        foreach ($invoices as $invoice) {
            $daysOverdue = now()->diffInDays($invoice->due_date, false);
            
            if ($daysOverdue >= 0) {
                $bucket = 'current';
            } elseif ($daysOverdue >= -30) {
                $bucket = '1_30';
            } elseif ($daysOverdue >= -60) {
                $bucket = '31_60';
            } elseif ($daysOverdue >= -90) {
                $bucket = '61_90';
            } else {
                $bucket = 'over_90';
            }

            $aging[$bucket]['count']++;
            $aging[$bucket]['amount'] += $invoice->balance;
            $aging[$bucket]['invoices'][] = $invoice;
        }

        return response()->json([
            'aging' => $aging,
            'total_receivable' => $invoices->sum('balance'),
            'total_overdue' => $invoices->filter(fn($i) => $i->isOverdue())->sum('balance'),
        ]);
    }

    public function summary()
    {
        return response()->json([
            'total_receivable' => CustomerInvoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])->sum('balance'),
            'overdue_amount' => CustomerInvoice::where('status', 'overdue')->sum('balance'),
            'pending_invoices' => CustomerInvoice::where('status', 'draft')->count(),
            'this_month_collected' => CustomerPayment::where('status', 'confirmed')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
        ]);
    }

    public function destroy(CustomerInvoice $customerInvoice)
    {
        if ($customerInvoice->status !== 'draft') {
            return response()->json(['message' => 'لا يمكن حذف فاتورة غير مسودة'], 422);
        }

        $customerInvoice->delete();
        return response()->json(['message' => 'تم حذف الفاتورة']);
    }
}
