<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_plan_item_id',
        'counted_by',
        'counted_at',
        'counted_qty',
        'note',
        'photo_path',
        'asset_id',
        'counted_quantity',
        'condition',
        'notes',
        'location_found',
        'photo',
        'plan_item_id'
    ];

    protected $casts = [
        'counted_at' => 'datetime'
    ];

    // Accessors pre kompatibilitu
    public function getActualQtyAttribute()
    {
        return $this->counted_qty;
    }

    public function getNotesAttribute()
    {
        return $this->note;
    }

    public function planItem()
    {
        return $this->belongsTo(\App\Models\InventoryPlanItem::class, 'plan_item_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\InventoryPlanItem::class, 'inventory_plan_item_id');
    }

    public function inventoryPlanItem()
    {
        return $this->belongsTo(\App\Models\InventoryPlanItem::class, 'inventory_plan_item_id');
    }

    public function asset()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'asset_id');
    }

    public function counter()
    {
        return $this->belongsTo(\App\Models\User::class, 'counted_by');
    }

    /**
     * Vzťah k inventarizačnému plánu cez položku plánu
     */
    public function inventoryPlan()
    {
        return $this->hasOneThrough(
            \App\Models\InventoryPlan::class,
            \App\Models\InventoryPlanItem::class,
            'id', // Foreign key on InventoryPlanItem
            'id', // Foreign key on InventoryPlan
            'inventory_plan_item_id', // Local key on InventoryCount
            'inventory_plan_id' // Local key on InventoryPlanItem
        );
    }
}
