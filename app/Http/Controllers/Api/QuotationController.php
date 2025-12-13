<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationTemplate;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::with(['lead', 'creator'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->lead_id, fn($q, $id) => $q->where('lead_id', $id))
            ->when($request->search, function($q, $search) {
                $q->where(function($query) use ($search) {
                    $query->where('quotation_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->latest();

        $quotations = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($quotations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'site_visit_id' => 'nullable|exists:site_visits,id',
            'template_id' => 'nullable|exists:quotation_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'valid_until' => 'required|date|after:today',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.category' => 'nullable|string',
            'items.*.name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.unit' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.cost_price' => 'nullable|numeric|min:0',
        ]);

        $quotation = Quotation::create([
            'lead_id' => $request->lead_id,
            'site_visit_id' => $request->site_visit_id,
            'template_id' => $request->template_id,
            'created_by' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'discount_amount' => $request->discount_amount ?? 0,
            'discount_type' => $request->discount_type ?? 'fixed',
            'tax_rate' => $request->tax_rate ?? 0,
            'valid_until' => $request->valid_until,
            'terms_conditions' => $request->terms_conditions,
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $index => $item) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'category' => $item['category'] ?? null,
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'unit' => $item['unit'] ?? 'unit',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'cost_price' => $item['cost_price'] ?? 0,
                'sort_order' => $index,
            ]);
        }

        $quotation->calculateTotals();

        return $this->successResponse($quotation->load(['lead', 'items']), 'تم إنشاء عرض السعر بنجاح', 201);
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['lead', 'siteVisit', 'creator', 'items', 'template']);

        return $this->successResponse($quotation);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'valid_until' => 'sometimes|date',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:draft,sent,viewed,accepted,rejected,expired,revised',
        ]);

        $quotation->update($request->only([
            'title', 'description', 'discount_amount', 'discount_type',
            'tax_rate', 'valid_until', 'terms_conditions', 'notes', 'status'
        ]));

        if ($request->status === 'accepted' && !$quotation->accepted_at) {
            $quotation->update(['accepted_at' => now()]);
        }

        $quotation->calculateTotals();

        return $this->successResponse($quotation->load(['lead', 'items']), 'تم تحديث عرض السعر بنجاح');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();

        return $this->successResponse(null, 'تم حذف عرض السعر بنجاح');
    }

    public function addItem(Request $request, Quotation $quotation)
    {
        $request->validate([
            'category' => 'nullable|string',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'unit' => 'nullable|string',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
        ]);

        $item = QuotationItem::create([
            'quotation_id' => $quotation->id,
            'category' => $request->category,
            'name' => $request->name,
            'description' => $request->description,
            'unit' => $request->unit ?? 'unit',
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'cost_price' => $request->cost_price ?? 0,
            'sort_order' => $quotation->items()->count(),
        ]);

        return $this->successResponse($item, 'تم إضافة البند بنجاح', 201);
    }

    public function updateItem(Request $request, Quotation $quotation, QuotationItem $item)
    {
        $request->validate([
            'category' => 'nullable|string',
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'unit' => 'nullable|string',
            'quantity' => 'sometimes|numeric|min:0',
            'unit_price' => 'sometimes|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
        ]);

        $item->update($request->all());

        return $this->successResponse($item, 'تم تحديث البند بنجاح');
    }

    public function deleteItem(Quotation $quotation, QuotationItem $item)
    {
        $item->delete();

        return $this->successResponse(null, 'تم حذف البند بنجاح');
    }

    public function send(Quotation $quotation)
    {
        $quotation->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $this->successResponse($quotation, 'تم إرسال عرض السعر بنجاح');
    }

    public function generatePdf(Quotation $quotation)
    {
        $quotation->load(['lead', 'items', 'creator']);

        $pdf = Pdf::loadView('pdf.quotation', compact('quotation'));
        
        $filename = "quotation-{$quotation->quotation_number}.pdf";
        $path = "quotations/{$filename}";
        
        \Storage::disk('public')->put($path, $pdf->output());
        
        $quotation->update(['pdf_path' => $path]);

        return $this->successResponse([
            'pdf_url' => \Storage::disk('public')->url($path),
        ], 'تم إنشاء ملف PDF بنجاح');
    }

    public function viewByToken($token)
    {
        $quotation = Quotation::where('view_token', $token)->firstOrFail();
        
        if (!$quotation->viewed_at) {
            $quotation->update([
                'viewed_at' => now(),
                'status' => $quotation->status === 'sent' ? 'viewed' : $quotation->status,
            ]);
        }

        $quotation->load(['lead', 'items']);

        return $this->successResponse($quotation);
    }

    public function templates()
    {
        $templates = QuotationTemplate::where('is_active', true)->get();

        return $this->successResponse($templates);
    }

    public function duplicate(Quotation $quotation)
    {
        $newQuotation = $quotation->replicate();
        $newQuotation->quotation_number = null;
        $newQuotation->status = 'draft';
        $newQuotation->sent_at = null;
        $newQuotation->viewed_at = null;
        $newQuotation->accepted_at = null;
        $newQuotation->view_token = null;
        $newQuotation->valid_until = now()->addDays(30);
        $newQuotation->save();

        foreach ($quotation->items as $item) {
            $newItem = $item->replicate();
            $newItem->quotation_id = $newQuotation->id;
            $newItem->save();
        }

        return $this->successResponse($newQuotation->load('items'), 'تم نسخ عرض السعر بنجاح', 201);
    }
}
