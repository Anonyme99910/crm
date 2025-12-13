<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category_id',
        'unit',
        'unit_price',
        'current_stock',
        'minimum_stock',
        'location',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function supplierPrices()
    {
        return $this->hasMany(SupplierPrice::class);
    }

    public function projectMaterials()
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function adjustStock($quantity, $type, $userId, $projectId = null, $notes = null, $reference = null)
    {
        $stockBefore = $this->current_stock;
        $stockAfter = $type === 'in' || $type === 'return' 
            ? $stockBefore + $quantity 
            : $stockBefore - $quantity;

        InventoryTransaction::create([
            'material_id' => $this->id,
            'project_id' => $projectId,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $notes,
            'reference' => $reference,
        ]);

        $this->update(['current_stock' => $stockAfter]);
    }
}
