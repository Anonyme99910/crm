<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplierBill;
use App\Models\SupplierBillItem;
use App\Models\SupplierPayment;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierBillController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierBill::with(['supplier', 'project', 'purchaseOrder', 'createdBy']);

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('matching_status')) {
            $query->where('matching_status', $request->matching_status);
        }
        if ($request->has('from_date')) {
            $query->where('bill_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('bill_date', '<=', $request->to_date);
        }

        $bills = $query->orderBy('bill_date', 'desc')->paginate(20);
        return response()->json($bills);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'project_id' => 'nullable|exists:projects,id',
            'supplier_invoice_number' => 'required|string|max:100',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.material_id' => 'nullable|exists:materials,id',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        $bill = DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemTax = $itemTotal * (($item['tax_rate'] ?? 0) / 100);
                $subtotal += $itemTotal;
                $taxAmount += $itemTax;
            }

            $totalAmount = $subtotal + $taxAmount;

            $bill = SupplierBill::create([
                'supplier_id' => $validated['supplier_id'],
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'supplier_invoice_number' => $validated['supplier_invoice_number'],
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'draft',
            ]);

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemTax = $itemTotal * (($item['tax_rate'] ?? 0) / 100);

                SupplierBillItem::create([
                    'supplier_bill_id' => $bill->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'material_id' => $item['material_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'unit',
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $itemTax,
                    'total' => $itemTotal + $itemTax,
                    'account_id' => $item['account_id'] ?? null,
                ]);
            }

            $bill->performThreeWayMatch();

            return $bill;
        });

        return response()->json($bill->load(['supplier', 'items', 'createdBy']), 201);
    }

    public function show(SupplierBill $supplierBill)
    {
        return response()->json($supplierBill->load(['supplier', 'project', 'purchaseOrder', 'items.material', 'payments', 'createdBy', 'approvedBy']));
    }

    public function approve(SupplierBill $supplierBill)
    {
        if (!in_array($supplierBill->status, ['draft', 'pending_approval'])) {
            return response()->json(['message' => 'Bill cannot be approved'], 422);
        }

        $supplierBill->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json($supplierBill->fresh());
    }

    public function markGoodsReceived(Request $request, SupplierBill $supplierBill)
    {
        $validated = $request->validate([
            'goods_received_date' => 'required|date',
        ]);

        $supplierBill->update([
            'goods_received' => true,
            'goods_received_date' => $validated['goods_received_date'],
        ]);

        $supplierBill->performThreeWayMatch();

        return response()->json($supplierBill->fresh());
    }

    public function makePayment(Request $request, SupplierBill $supplierBill)
    {
        if (!in_array($supplierBill->status, ['approved', 'partially_paid'])) {
            return response()->json(['message' => 'Bill must be approved before payment'], 422);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $supplierBill->balance,
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,bank_transfer,credit_card',
            'reference_number' => 'nullable|string|max:100',
            'check_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $bankAccount = BankAccount::findOrFail($validated['bank_account_id']);

        if ($bankAccount->current_balance < $validated['amount']) {
            return response()->json(['message' => 'Insufficient balance in selected account'], 422);
        }

        $payment = DB::transaction(function () use ($supplierBill, $validated, $bankAccount) {
            $newBalance = $bankAccount->current_balance - $validated['amount'];

            $payment = SupplierPayment::create([
                'supplier_id' => $supplierBill->supplier_id,
                'supplier_bill_id' => $supplierBill->id,
                'bank_account_id' => $validated['bank_account_id'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'check_number' => $validated['check_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'completed',
            ]);

            BankTransaction::create([
                'bank_account_id' => $bankAccount->id,
                'type' => 'withdrawal',
                'amount' => $validated['amount'],
                'balance_after' => $newBalance,
                'description' => 'Supplier Payment: ' . $supplierBill->supplier->name . ' - ' . $supplierBill->bill_number,
                'transaction_date' => $validated['payment_date'],
                'reference_number' => $payment->payment_number,
                'transactionable_type' => SupplierPayment::class,
                'transactionable_id' => $payment->id,
                'created_by' => auth()->id(),
                'status' => 'completed',
            ]);

            $bankAccount->update(['current_balance' => $newBalance]);

            $supplierBill->updatePaymentStatus();

            return $payment;
        });

        return response()->json($payment->load(['supplier', 'supplierBill', 'bankAccount']), 201);
    }

    public function agingReport()
    {
        $bills = SupplierBill::whereIn('status', ['approved', 'partially_paid'])
            ->where('balance', '>', 0)
            ->with('supplier')
            ->get();

        $aging = [
            'current' => ['count' => 0, 'amount' => 0, 'bills' => []],
            '1_30' => ['count' => 0, 'amount' => 0, 'bills' => []],
            '31_60' => ['count' => 0, 'amount' => 0, 'bills' => []],
            '61_90' => ['count' => 0, 'amount' => 0, 'bills' => []],
            'over_90' => ['count' => 0, 'amount' => 0, 'bills' => []],
        ];

        foreach ($bills as $bill) {
            $daysOverdue = now()->diffInDays($bill->due_date, false);
            
            if ($daysOverdue >= 0) {
                $category = 'current';
            } elseif ($daysOverdue >= -30) {
                $category = '1_30';
            } elseif ($daysOverdue >= -60) {
                $category = '31_60';
            } elseif ($daysOverdue >= -90) {
                $category = '61_90';
            } else {
                $category = 'over_90';
            }

            $aging[$category]['count']++;
            $aging[$category]['amount'] += $bill->balance;
            $aging[$category]['bills'][] = [
                'id' => $bill->id,
                'bill_number' => $bill->bill_number,
                'supplier' => $bill->supplier->name,
                'due_date' => $bill->due_date,
                'balance' => $bill->balance,
                'days_overdue' => abs($daysOverdue),
            ];
        }

        return response()->json($aging);
    }
}
