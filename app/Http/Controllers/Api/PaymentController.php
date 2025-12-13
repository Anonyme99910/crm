<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\ProjectBudget;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    public function invoices(Request $request)
    {
        $query = Invoice::with(['project', 'creator'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->project_id, fn($q, $id) => $q->where('project_id', $id))
            ->when($request->overdue, fn($q) => $q->where('due_date', '<', now())->where('status', '!=', 'paid'))
            ->latest();

        $invoices = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($invoices);
    }

    public function createInvoice(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'payment_term_id' => 'nullable|exists:contract_payment_terms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice = Invoice::create([
            'project_id' => $request->project_id,
            'contract_id' => $request->contract_id,
            'payment_term_id' => $request->payment_term_id,
            'created_by' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'tax_rate' => $request->tax_rate ?? 0,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
        ]);

        $subtotal = 0;
        foreach ($request->items as $item) {
            $total = $item['quantity'] * $item['unit_price'];
            $subtotal += $total;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $total,
            ]);
        }

        $taxAmount = $subtotal * ($invoice->tax_rate / 100);
        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
        ]);

        return $this->successResponse($invoice->load('items'), 'تم إنشاء الفاتورة بنجاح', 201);
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load(['project.lead', 'contract', 'creator', 'items', 'payments']);

        return $this->successResponse($invoice);
    }

    public function updateInvoice(Request $request, Invoice $invoice)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:draft,sent,paid,partial,overdue,cancelled',
            'due_date' => 'sometimes|date',
        ]);

        $invoice->update($request->all());

        return $this->successResponse($invoice, 'تم تحديث الفاتورة بنجاح');
    }

    public function sendInvoice(Invoice $invoice)
    {
        $invoice->update(['status' => 'sent']);

        return $this->successResponse($invoice, 'تم إرسال الفاتورة بنجاح');
    }

    public function generateInvoicePdf(Invoice $invoice)
    {
        $invoice->load(['project.lead', 'items']);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        
        $filename = "invoice-{$invoice->invoice_number}.pdf";
        $path = "invoices/{$filename}";
        
        \Storage::disk('public')->put($path, $pdf->output());
        
        $invoice->update(['pdf_path' => $path]);

        return $this->successResponse([
            'pdf_url' => \Storage::disk('public')->url($path),
        ], 'تم إنشاء ملف PDF بنجاح');
    }

    public function payments(Request $request)
    {
        $query = Payment::with(['invoice', 'project', 'receiver'])
            ->when($request->project_id, fn($q, $id) => $q->where('project_id', $id))
            ->when($request->method, fn($q, $method) => $q->where('method', $method))
            ->when($request->date_from, fn($q, $date) => $q->whereDate('payment_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->whereDate('payment_date', '<=', $date))
            ->latest('payment_date');

        $payments = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($payments);
    }

    public function recordPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank_transfer,check,credit_card,other',
            'reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|max:5120',
        ]);

        $data = $request->except('receipt');
        $data['received_by'] = auth()->id();

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        $payment = Payment::create($data);

        return $this->successResponse($payment->load(['invoice', 'project']), 'تم تسجيل الدفعة بنجاح', 201);
    }

    public function expenses(Request $request)
    {
        $query = Expense::with(['project', 'supplier', 'creator'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->project_id, fn($q, $id) => $q->where('project_id', $id))
            ->when($request->category, fn($q, $category) => $q->where('category', $category))
            ->when($request->date_from, fn($q, $date) => $q->whereDate('expense_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->whereDate('expense_date', '<=', $date))
            ->latest('expense_date');

        $expenses = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($expenses);
    }

    public function createExpense(Request $request)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|max:5120',
        ]);

        $data = $request->except('receipt');
        $data['created_by'] = auth()->id();

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('expenses', 'public');
        }

        $expense = Expense::create($data);

        return $this->successResponse($expense, 'تم تسجيل المصروف بنجاح', 201);
    }

    public function approveExpense(Expense $expense)
    {
        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return $this->successResponse($expense, 'تم اعتماد المصروف بنجاح');
    }

    public function projectBudget($projectId)
    {
        $budgets = ProjectBudget::where('project_id', $projectId)->get();

        $summary = [
            'total_budgeted' => $budgets->sum('budgeted_amount'),
            'total_actual' => $budgets->sum('actual_amount'),
            'variance' => $budgets->sum('budgeted_amount') - $budgets->sum('actual_amount'),
            'categories' => $budgets,
        ];

        return $this->successResponse($summary);
    }

    public function updateProjectBudget(Request $request, $projectId)
    {
        $request->validate([
            'budgets' => 'required|array',
            'budgets.*.category' => 'required|string',
            'budgets.*.budgeted_amount' => 'required|numeric|min:0',
            'budgets.*.actual_amount' => 'nullable|numeric|min:0',
            'budgets.*.notes' => 'nullable|string',
        ]);

        foreach ($request->budgets as $budget) {
            ProjectBudget::updateOrCreate(
                [
                    'project_id' => $projectId,
                    'category' => $budget['category'],
                ],
                [
                    'budgeted_amount' => $budget['budgeted_amount'],
                    'actual_amount' => $budget['actual_amount'] ?? 0,
                    'notes' => $budget['notes'] ?? null,
                ]
            );
        }

        return $this->successResponse(null, 'تم تحديث الموازنة بنجاح');
    }

    public function financialReport(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth();
        $dateTo = $request->date_to ?? now()->endOfMonth();

        $report = [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'income' => [
                'total_invoiced' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])->sum('total'),
                'total_collected' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->sum('amount'),
                'outstanding' => Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->sum('total') 
                    - Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->sum('paid_amount'),
            ],
            'expenses' => [
                'total' => Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
                    ->where('status', 'approved')
                    ->sum('amount'),
                'by_category' => Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
                    ->where('status', 'approved')
                    ->selectRaw('category, SUM(amount) as total')
                    ->groupBy('category')
                    ->pluck('total', 'category'),
            ],
            'overdue_invoices' => Invoice::where('due_date', '<', now())
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->count(),
        ];

        $report['net_income'] = $report['income']['total_collected'] - $report['expenses']['total'];

        return $this->successResponse($report);
    }
}
