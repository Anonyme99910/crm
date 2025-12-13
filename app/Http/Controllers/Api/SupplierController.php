<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPrice;
use App\Models\SupplierRating;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query()
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->search, function($q, $search) {
                $q->where(function($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->is_active))
            ->orderBy('name');

        $suppliers = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($suppliers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'type' => 'required|in:supplier,contractor,both',
            'specialization' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $supplier = Supplier::create($request->all());

        return $this->successResponse($supplier, 'تم إضافة المورد بنجاح', 201);
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'prices.material',
            'ratings' => fn($q) => $q->latest()->limit(10),
            'purchaseOrders' => fn($q) => $q->latest()->limit(10),
        ]);

        return $this->successResponse($supplier);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'type' => 'sometimes|in:supplier,contractor,both',
            'specialization' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update($request->all());

        return $this->successResponse($supplier, 'تم تحديث بيانات المورد بنجاح');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return $this->successResponse(null, 'تم حذف المورد بنجاح');
    }

    public function addPrice(Request $request, Supplier $supplier)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'unit_price' => 'required|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_preferred' => 'boolean',
        ]);

        $price = SupplierPrice::updateOrCreate(
            [
                'supplier_id' => $supplier->id,
                'material_id' => $request->material_id,
            ],
            $request->only(['unit_price', 'minimum_order', 'lead_time_days', 'valid_from', 'valid_until', 'is_preferred'])
        );

        return $this->successResponse($price->load('material'), 'تم تحديث السعر بنجاح');
    }

    public function addRating(Request $request, Supplier $supplier)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'quality_rating' => 'required|integer|min:1|max:5',
            'delivery_rating' => 'required|integer|min:1|max:5',
            'price_rating' => 'required|integer|min:1|max:5',
            'communication_rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
        ]);

        $rating = SupplierRating::create([
            'supplier_id' => $supplier->id,
            'user_id' => auth()->id(),
            ...$request->all(),
        ]);

        return $this->successResponse($rating, 'تم إضافة التقييم بنجاح', 201);
    }

    public function purchaseOrders(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'project', 'creator'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->project_id, fn($q, $id) => $q->where('project_id', $id))
            ->latest();

        $orders = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($orders);
    }

    public function createPurchaseOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'project_id' => 'nullable|exists:projects,id',
            'expected_delivery' => 'nullable|date',
            'notes' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $request->supplier_id,
            'project_id' => $request->project_id,
            'created_by' => auth()->id(),
            'expected_delivery' => $request->expected_delivery,
            'notes' => $request->notes,
            'delivery_address' => $request->delivery_address,
        ]);

        foreach ($request->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'material_id' => $item['material_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        $po->calculateTotals();

        return $this->successResponse($po->load(['supplier', 'items.material']), 'تم إنشاء أمر الشراء بنجاح', 201);
    }

    public function showPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'project', 'creator', 'approver', 'items.material']);

        return $this->successResponse($purchaseOrder);
    }

    public function updatePurchaseOrderStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'status' => 'required|in:draft,pending_approval,approved,sent,partial,received,cancelled',
        ]);

        $data = ['status' => $request->status];

        if ($request->status === 'approved') {
            $data['approved_by'] = auth()->id();
        }

        if ($request->status === 'received') {
            $data['actual_delivery'] = now();
        }

        $purchaseOrder->update($data);

        return $this->successResponse($purchaseOrder, 'تم تحديث حالة أمر الشراء بنجاح');
    }

    public function receivePurchaseOrderItems(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        foreach ($request->items as $itemData) {
            $item = PurchaseOrderItem::find($itemData['id']);
            $item->update(['received_quantity' => $itemData['received_quantity']]);

            if ($itemData['received_quantity'] > 0) {
                $item->material->adjustStock(
                    $itemData['received_quantity'],
                    'in',
                    auth()->id(),
                    $purchaseOrder->project_id,
                    "استلام من أمر شراء {$purchaseOrder->po_number}",
                    $purchaseOrder->po_number
                );
            }
        }

        $allReceived = $purchaseOrder->items->every(fn($i) => $i->received_quantity >= $i->quantity);
        $partialReceived = $purchaseOrder->items->some(fn($i) => $i->received_quantity > 0);

        $purchaseOrder->update([
            'status' => $allReceived ? 'received' : ($partialReceived ? 'partial' : $purchaseOrder->status),
            'actual_delivery' => $allReceived ? now() : $purchaseOrder->actual_delivery,
        ]);

        return $this->successResponse($purchaseOrder->load('items'), 'تم تسجيل الاستلام بنجاح');
    }

    public function statistics()
    {
        $stats = [
            'total_suppliers' => Supplier::where('type', '!=', 'contractor')->count(),
            'total_contractors' => Supplier::where('type', '!=', 'supplier')->count(),
            'active_suppliers' => Supplier::where('is_active', true)->count(),
            'pending_orders' => PurchaseOrder::whereIn('status', ['pending_approval', 'approved', 'sent'])->count(),
            'total_orders_value' => PurchaseOrder::where('status', '!=', 'cancelled')->sum('total'),
        ];

        return $this->successResponse($stats);
    }
}
