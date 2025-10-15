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
     * Boot method pre automatické generovanie inventárneho čísla a QR kódu
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
                
                // Automatické generovanie QR kódu
                try {
                    $qrCodeService = app(\App\Services\QrCodeService::class);
                    $qrCodeService->generateQrCode($asset);
                } catch (\Exception $e) {
                    \Log::warning("Nepodarilo sa vygenerovať QR kód pre asset {$asset->id}: " . $e->getMessage());
                }
            }
        });

        static::updated(function ($asset) {
            // Ak sa zmenil inventory_number, regeneruj QR kód
            if ($asset->isDirty('inventory_number')) {
                try {
                    $qrCodeService = app(\App\Services\QrCodeService::class);
                    $qrCodeService->generateQrCode($asset);
                } catch (\Exception $e) {
                    \Log::warning("Nepodarilo sa regenerovať QR kód pre asset {$asset->id}: " . $e->getMessage());
                }
            }
        });

        static::deleted(function ($asset) {
            // Vymaž QR kód pri zmazaní assetu
            try {
                $qrCodeService = app(\App\Services\QrCodeService::class);
                $qrCodeService->deleteQrCode($asset);
            } catch (\Exception $e) {
                \Log::warning("Nepodarilo sa vymazať QR kód pre asset {$asset->id}: " . $e->getMessage());
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

    /**
     * Get QR code URL for this asset
     */
    public function getQrCodeUrl(): ?string
    {
        try {
            $qrCodeService = app(\App\Services\QrCodeService::class);
            return $qrCodeService->getQrCodeUrl($this);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if QR code exists for this asset
     */
    public function hasQrCode(): bool
    {
        return $this->getQrCodeUrl() !== null;
    }

    /**
     * Generate QR code for this asset
     */
    public function generateQrCode(): bool
    {
        try {
            $qrCodeService = app(\App\Services\QrCodeService::class);
            $filename = $qrCodeService->generateQrCode($this);
            return $filename !== null;
        } catch (\Exception $e) {
            return false;
        }
    }
}
