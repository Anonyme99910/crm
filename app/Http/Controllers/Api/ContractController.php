<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractPaymentTerm;
use App\Models\ContractAmendment;
use App\Models\Document;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with(['project', 'creator'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->project_id, fn($q, $id) => $q->where('project_id', $id))
            ->when($request->search, function($q, $search) {
                $q->where(function($query) use ($search) {
                    $query->where('contract_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->latest();

        $contracts = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($contracts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'terms_conditions' => 'nullable|string',
            'scope_of_work' => 'nullable|string',
            'payment_terms' => 'nullable|array',
            'payment_terms.*.description' => 'required|string',
            'payment_terms.*.percentage' => 'required|numeric|min:0|max:100',
            'payment_terms.*.due_date' => 'nullable|date',
            'payment_terms.*.milestone' => 'nullable|string',
        ]);

        $contract = Contract::create([
            ...$request->except('payment_terms'),
            'created_by' => auth()->id(),
        ]);

        if ($request->payment_terms) {
            foreach ($request->payment_terms as $index => $term) {
                ContractPaymentTerm::create([
                    'contract_id' => $contract->id,
                    'description' => $term['description'],
                    'percentage' => $term['percentage'],
                    'amount' => ($term['percentage'] / 100) * $contract->total_value,
                    'due_date' => $term['due_date'] ?? null,
                    'milestone' => $term['milestone'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }

        return $this->successResponse($contract->load(['project', 'paymentTerms']), 'تم إنشاء العقد بنجاح', 201);
    }

    public function show(Contract $contract)
    {
        $contract->load([
            'project.lead',
            'quotation',
            'creator',
            'paymentTerms',
            'amendments',
            'documents',
            'invoices',
        ]);

        return $this->successResponse($contract);
    }

    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:draft,pending_signature,active,completed,terminated,expired',
            'total_value' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'terms_conditions' => 'nullable|string',
            'scope_of_work' => 'nullable|string',
        ]);

        $contract->update($request->all());

        return $this->successResponse($contract, 'تم تحديث العقد بنجاح');
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();

        return $this->successResponse(null, 'تم حذف العقد بنجاح');
    }

    public function addPaymentTerm(Request $request, Contract $contract)
    {
        $request->validate([
            'description' => 'required|string',
            'percentage' => 'required|numeric|min:0|max:100',
            'due_date' => 'nullable|date',
            'milestone' => 'nullable|string',
        ]);

        $term = ContractPaymentTerm::create([
            'contract_id' => $contract->id,
            'description' => $request->description,
            'percentage' => $request->percentage,
            'amount' => ($request->percentage / 100) * $contract->total_value,
            'due_date' => $request->due_date,
            'milestone' => $request->milestone,
            'sort_order' => $contract->paymentTerms()->count(),
        ]);

        return $this->successResponse($term, 'تم إضافة شرط الدفع بنجاح', 201);
    }

    public function createAmendment(Request $request, Contract $contract)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'value_change' => 'nullable|numeric',
            'time_extension_days' => 'nullable|integer|min:0',
        ]);

        $amendmentNumber = $contract->contract_number . '-A' . ($contract->amendments()->count() + 1);

        $amendment = ContractAmendment::create([
            'contract_id' => $contract->id,
            'created_by' => auth()->id(),
            'amendment_number' => $amendmentNumber,
            'title' => $request->title,
            'description' => $request->description,
            'value_change' => $request->value_change ?? 0,
            'time_extension_days' => $request->time_extension_days ?? 0,
        ]);

        return $this->successResponse($amendment, 'تم إنشاء أمر التعديل بنجاح', 201);
    }

    public function approveAmendment(ContractAmendment $amendment)
    {
        $amendment->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $contract = $amendment->contract;
        $contract->update([
            'total_value' => $contract->total_value + $amendment->value_change,
            'end_date' => $contract->end_date->addDays($amendment->time_extension_days),
        ]);

        return $this->successResponse($amendment, 'تم اعتماد أمر التعديل بنجاح');
    }

    public function uploadDocument(Request $request, Contract $contract)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('contracts/' . $contract->id, 'public');

        $document = Document::create([
            'documentable_type' => Contract::class,
            'documentable_id' => $contract->id,
            'uploaded_by' => auth()->id(),
            'name' => $request->name,
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'category' => $request->category,
            'description' => $request->description,
        ]);

        return $this->successResponse($document, 'تم رفع المستند بنجاح', 201);
    }

    public function sign(Request $request, Contract $contract)
    {
        $request->validate([
            'signature' => 'required|string',
            'type' => 'required|in:client,company',
        ]);

        if ($request->type === 'client') {
            $contract->update([
                'client_signature' => $request->signature,
                'client_signed_at' => now(),
            ]);
        } else {
            $contract->update([
                'company_signature' => $request->signature,
                'company_signed_at' => now(),
            ]);
        }

        if ($contract->isSigned()) {
            $contract->update(['status' => 'active']);
        }

        return $this->successResponse($contract, 'تم التوقيع بنجاح');
    }

    public function generatePdf(Contract $contract)
    {
        $contract->load(['project.lead', 'paymentTerms', 'amendments']);

        $pdf = Pdf::loadView('pdf.contract', compact('contract'));
        
        $filename = "contract-{$contract->contract_number}.pdf";
        $path = "contracts/{$filename}";
        
        \Storage::disk('public')->put($path, $pdf->output());
        
        $contract->update(['pdf_path' => $path]);

        return $this->successResponse([
            'pdf_url' => \Storage::disk('public')->url($path),
        ], 'تم إنشاء ملف PDF بنجاح');
    }
}
