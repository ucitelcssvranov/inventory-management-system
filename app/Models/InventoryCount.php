<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_plan_item_id','counted_by','counted_at','counted_qty','note','photo_path'
    ];

    protected $casts = [
        'counted_at' => 'datetime'
    ];

    public function item()
    {
        return $this->belongsTo(\App\Models\InventoryPlanItem::class, 'inventory_plan_item_id');
    }

    public function inventoryPlanItem()
    {
        return $this->belongsTo(\App\Models\InventoryPlanItem::class, 'inventory_plan_item_id');
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
