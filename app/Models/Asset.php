<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\InventoryNumberService;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inventory_number','name','description','category_id','location_id',
        'custodian_user_id','serial_number','acquisition_date','acquisition_cost',
        'residual_value','condition','status','active','metadata',
        'inventory_commission', 'owner'
    ];

    protected $casts = [
        'metadata' => 'array',
        'acquisition_date' => 'date',
        'active' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    public function custodian()
    {
        return $this->belongsTo(\App\Models\User::class, 'custodian_user_id');
    }

    public function planItems()
    {
        return $this->hasMany(\App\Models\InventoryPlanItem::class);
    }

    /**
     * Scope pre nepriradený majetok v inventarizačnom pláne
     */
    public function scopeUnassignedInPlan($query, $planId)
    {
        return $query->whereHas('planItems', function ($q) use ($planId) {
            $q->where('inventory_plan_id', $planId)
              ->where('assignment_status', \App\Models\InventoryPlanItem::ASSIGNMENT_UNASSIGNED);
        });
    }

    /**
     * Kontrola, či je majetok priradený v konkrétnom pláne
     */
    public function isAssignedInPlan($planId)
    {
        return $this->planItems()
                   ->where('inventory_plan_id', $planId)
                   ->where('assignment_status', '!=', \App\Models\InventoryPlanItem::ASSIGNMENT_UNASSIGNED)
                   ->exists();
    }

    /**
     * Získanie stavu inventarizácie v konkrétnom pláne
     */
    public function getInventoryStatusInPlan($planId)
    {
        $planItem = $this->planItems()
                        ->where('inventory_plan_id', $planId)
                        ->first();
        
        return $planItem ? $planItem->assignment_status : null;
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Boot method pre automatické generovanie inventárneho čísla
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($asset) {
            if (empty($asset->inventory_number) && $asset->acquisition_date) {
                $inventoryService = new InventoryNumberService();
                $asset->inventory_number = $inventoryService->generateInventoryNumber(
                    $asset->id, 
                    $asset->acquisition_date
                );
                $asset->saveQuietly(); // Uloží bez triggerovania eventov
            }
        });
    }

    /**
     * Generuje nové inventárne číslo pre tento majetok
     * 
     * @return string
     */
    public function generateInventoryNumber(): string
    {
        $inventoryService = new InventoryNumberService();
        return $inventoryService->generateInventoryNumber($this->id, $this->acquisition_date);
    }

    /**
     * Regeneruje inventárne číslo pre tento majetok a uloží ho
     * 
     * @return bool
     */
    public function regenerateInventoryNumber(): bool
    {
        if ($this->acquisition_date) {
            $this->inventory_number = $this->generateInventoryNumber();
            return $this->save();
        }
        return false;
    }

    /**
     * Kontroluje, či je inventárne číslo konzistentné s dátumom nadobudnutia
     * 
     * @return bool
     */
    public function hasValidInventoryNumber(): bool
    {
        if (empty($this->inventory_number) || empty($this->acquisition_date)) {
            return false;
        }

        $inventoryService = new InventoryNumberService();
        return $inventoryService->isInventoryNumberValidForAcquisitionDate(
            $this->inventory_number, 
            $this->acquisition_date
        );
    }

    /**
     * Statická metóda pre vygenerovanie dočasného inventárneho čísla
     * Používa sa pre preview v formulároch
     * 
     * @param string $acquisitionDate
     * @return string
     */
    public static function generateTemporaryInventoryNumber($acquisitionDate): string
    {
        $inventoryService = new InventoryNumberService();
        return $inventoryService->generateTemporaryInventoryNumber($acquisitionDate);
    }
}
