<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date_from',
        'date_to',
        'type',
        'status',
        'created_by',
        'planned_date',
        'location_id',
        'category_id',
        'description',
        'date',
        'inventory_day',
        'unit_name',
        'unit_address',
        'storage_place',
        'responsible_person_id',
        'commission_id',
        'date_start',
        'date_end',
        'updated_by',
        'process_status',
        'approved_by',
        'approved_at',
        'started_at',
        'completed_at',
        'signed_at',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'planned_date' => 'date',
        'date' => 'date',
        'inventory_day' => 'date',
        'date_start' => 'date',
        'date_end' => 'date',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    // Stavy procesu inventarizácie podľa zákona
    const STATUS_DRAFT = 'draft';           // Návrh
    const STATUS_PENDING = 'pending';       // Čaká na schválenie
    const STATUS_APPROVED = 'approved';     // Schválený
    const STATUS_ASSIGNED = 'assigned';     // Priradený komisii
    const STATUS_IN_PROGRESS = 'in_progress'; // Prebieha inventarizácia
    const STATUS_COMPLETED = 'completed';   // Dokončená inventarizácia
    const STATUS_SIGNED = 'signed';         // Podpísaná komisiou
    const STATUS_ARCHIVED = 'archived';     // Archivovaná

    // Typy inventarizácie
    const TYPE_FULL = 'full';              // Úplná inventarizácia
    const TYPE_PARTIAL = 'partial';        // Čiastočná inventarizácia
    const TYPE_EXTRAORDINARY = 'extraordinary'; // Mimoriadna inventarizácia

    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Návrh',
            self::STATUS_PENDING => 'Čaká na schválenie', 
            self::STATUS_APPROVED => 'Schválený',
            self::STATUS_ASSIGNED => 'Priradený komisii',
            self::STATUS_IN_PROGRESS => 'Prebieha inventarizácia',
            self::STATUS_COMPLETED => 'Dokončená inventarizácia',
            self::STATUS_SIGNED => 'Podpísaná komisiou',
            self::STATUS_ARCHIVED => 'Archivovaná'
        ];
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_FULL => 'Úplná inventarizácia',
            self::TYPE_PARTIAL => 'Čiastočná inventarizácia', 
            self::TYPE_EXTRAORDINARY => 'Mimoriadna inventarizácia'
        ];
    }

    public function getStatusLabelAttribute()
    {
        $statuses = self::getStatusOptions();
        return $statuses[$this->status] ?? 'Neznámy stav';
    }

    public function getTypeLabelAttribute()
    {
        $types = self::getTypeOptions();
        return $types[$this->type] ?? 'Neznámy typ';
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_ASSIGNED => 'primary',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_SIGNED => 'success',
            self::STATUS_ARCHIVED => 'dark'
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    public function items()
    {
        return $this->hasMany(\App\Models\InventoryPlanItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function commissionMembers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'inventory_plan_commission', 'inventory_plan_id', 'user_id')
            ->withPivot('role', 'signed_at', 'signature');
    }

    public function responsiblePerson()
    {
        return $this->belongsTo(\App\Models\User::class, 'responsible_person_id');
    }

    public function commission()
    {
        return $this->belongsTo(\App\Models\InventoryCommission::class, 'commission_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id');
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function counts()
    {
        return $this->hasManyThrough(
            \App\Models\InventoryCount::class,
            \App\Models\InventoryPlanItem::class,
            'inventory_plan_id', // Foreign key on InventoryPlanItem
            'inventory_plan_item_id', // Foreign key on InventoryCount
            'id', // Local key on InventoryPlan
            'id'  // Local key on InventoryPlanItem
        );
    }

    public function differences()
    {
        return $this->hasMany(\App\Models\InventoryDifference::class, 'inventory_plan_id');
    }

    /**
     * Získanie všetkých počítaní cez položky plánu (alternatívny prístup)
     */
    public function getAllCounts()
    {
        return \App\Models\InventoryCount::whereHas('inventoryPlanItem', function($query) {
            $query->where('inventory_plan_id', $this->id);
        });
    }

    // Scope metódy
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('commission_id');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('commission_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_ASSIGNED, 
            self::STATUS_IN_PROGRESS
        ]);
    }

    // Metódy pre workflow
    public function canBeAssigned()
    {
        return in_array($this->status, [self::STATUS_APPROVED]);
    }

    public function canBeStarted()
    {
        return $this->status === self::STATUS_ASSIGNED && $this->commission_id;
    }

    public function canBeCompleted()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canBeSigned()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function assignToCommission($commissionId, $userId = null)
    {
        $this->update([
            'commission_id' => $commissionId,
            'status' => self::STATUS_ASSIGNED,
            'updated_by' => $userId ?? auth()->id()
        ]);
    }

    public function startInventory($userId = null)
    {
        if (!$this->canBeStarted()) {
            throw new \Exception('Inventarizácia nemôže byť spustená v aktuálnom stave.');
        }

        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'updated_by' => $userId ?? auth()->id()
        ]);
    }

    public function completeInventory($userId = null)
    {
        if (!$this->canBeCompleted()) {
            throw new \Exception('Inventarizácia nemôže byť dokončená v aktuálnom stave.');
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'updated_by' => $userId ?? auth()->id()
        ]);
    }

    public function signInventory($userId = null)
    {
        if (!$this->canBeSigned()) {
            throw new \Exception('Inventarizácia nemôže byť podpísaná v aktuálnom stave.');
        }

        $this->update([
            'status' => self::STATUS_SIGNED,
            'signed_at' => now(),
            'updated_by' => $userId ?? auth()->id()
        ]);
    }

    // Validačné pravidlá
    public static function validationRules($id = null)
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(self::getTypeOptions())),
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'location_id' => 'nullable|exists:locations,id',
            'category_id' => 'nullable|exists:categories,id',
            'responsible_person_id' => 'required|exists:users,id',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
