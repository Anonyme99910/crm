<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\InventoryTransaction;
use App\Models\ProjectMaterial;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::with(['category'])
            ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
            ->when($request->low_stock, fn($q) => $q->whereRaw('current_stock <= minimum_stock'))
            ->when($request->search, function($q, $search) {
                $q->where(function($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->is_active))
            ->orderBy('name');

        $materials = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($materials);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:materials,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:material_categories,id',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $material = Material::create($request->all());

        if ($request->current_stock > 0) {
            InventoryTransaction::create([
                'material_id' => $material->id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity' => $request->current_stock,
                'stock_before' => 0,
                'stock_after' => $request->current_stock,
                'notes' => 'رصيد افتتاحي',
            ]);
        }

        return $this->successResponse($material, 'تم إضافة الخامة بنجاح', 201);
    }

    public function show(Material $material)
    {
        $material->load(['category', 'transactions' => fn($q) => $q->latest()->limit(20)]);

        return $this->successResponse($material);
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'code' => 'sometimes|string|unique:materials,code,' . $material->id,
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:material_categories,id',
            'unit' => 'sometimes|string|max:50',
            'unit_price' => 'sometimes|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $material->update($request->all());

        return $this->successResponse($material, 'تم تحديث الخامة بنجاح');
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return $this->successResponse(null, 'تم حذف الخامة بنجاح');
    }

    public function adjustStock(Request $request, Material $material)
    {
        $request->validate([
            'type' => 'required|in:in,out,adjustment,return',
            'quantity' => 'required|numeric|min:0.01',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        if ($request->type === 'out' && $material->current_stock < $request->quantity) {
            return $this->errorResponse('الكمية المطلوبة أكبر من المخزون المتاح', 422);
        }

        $material->adjustStock(
            $request->quantity,
            $request->type,
            auth()->id(),
            $request->project_id,
            $request->notes,
            $request->reference
        );

        return $this->successResponse($material->fresh(), 'تم تعديل المخزون بنجاح');
    }

    public function transactions(Request $request, Material $material)
    {
        $transactions = $material->transactions()
            ->with(['user', 'project'])
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return $this->paginatedResponse($transactions);
    }

    public function categories()
    {
        $categories = MaterialCategory::with('children')->whereNull('parent_id')->get();

        return $this->successResponse($categories);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:material_categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:material_categories,id',
        ]);

        $category = MaterialCategory::create($request->all());

        return $this->successResponse($category, 'تم إضافة التصنيف بنجاح', 201);
    }

    public function lowStock()
    {
        $materials = Material::with('category')
            ->whereRaw('current_stock <= minimum_stock')
            ->where('is_active', true)
            ->get();

        return $this->successResponse($materials);
    }

    public function projectMaterials(Request $request, $projectId)
    {
        $materials = ProjectMaterial::with(['material', 'phase'])
            ->where('project_id', $projectId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->get();

        return $this->successResponse($materials);
    }

    public function addProjectMaterial(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'material_id' => 'required|exists:materials,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'required_quantity' => 'required|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $material = Material::find($request->material_id);

        $projectMaterial = ProjectMaterial::create([
            'project_id' => $request->project_id,
            'material_id' => $request->material_id,
            'phase_id' => $request->phase_id,
            'required_quantity' => $request->required_quantity,
            'unit_price' => $request->unit_price ?? $material->unit_price,
        ]);

        return $this->successResponse($projectMaterial->load('material'), 'تم إضافة الخامة للمشروع بنجاح', 201);
    }

    public function statistics()
    {
        $stats = [
            'total_materials' => Material::count(),
            'active_materials' => Material::where('is_active', true)->count(),
            'low_stock_count' => Material::whereRaw('current_stock <= minimum_stock')->count(),
            'total_stock_value' => Material::selectRaw('SUM(current_stock * unit_price) as value')->value('value') ?? 0,
            'categories_count' => MaterialCategory::count(),
        ];

        return $this->successResponse($stats);
    }
}
