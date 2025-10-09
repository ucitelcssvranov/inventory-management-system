<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_plan_id',
        'asset_id',
        'expected_qty',
        'commission_id',
        'assignment_status',
        'assigned_at',
        'started_at',
        'completed_at',
        'assigned_by',
        'inventory_notes',
        'digital_updates'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'digital_updates' => 'array'
    ];

    // Stavy pridelenia majetku
    const ASSIGNMENT_UNASSIGNED = 'unassigned';
    const ASSIGNMENT_ASSIGNED = 'assigned';
    const ASSIGNMENT_IN_PROGRESS = 'in_progress';
    const ASSIGNMENT_COMPLETED = 'completed';
    const ASSIGNMENT_VERIFIED = 'verified';

    /**
     * Možnosti statusu pridelenia
     */
    public static function getAssignmentStatusOptions()
    {
        return [
            self::ASSIGNMENT_UNASSIGNED => 'Nepriradené',
            self::ASSIGNMENT_ASSIGNED => 'Priradené',
            self::ASSIGNMENT_IN_PROGRESS => 'Prebieha inventarizácia',
            self::ASSIGNMENT_COMPLETED => 'Dokončené',
            self::ASSIGNMENT_VERIFIED => 'Overené'
        ];
    }

    public function plan()
    {
        return $this->belongsTo(\App\Models\InventoryPlan::class, 'inventory_plan_id');
    }

    public function asset()
    {
        return $this->belongsTo(\App\Models\Asset::class);
    }

    public function counts()
    {
        return $this->hasMany(\App\Models\InventoryCount::class);
    }

    /**
     * Inventarizačná komisia, ktorej je položka priradená
     */
    public function commission()
    {
        return $this->belongsTo(\App\Models\InventoryCommission::class, 'commission_id');
    }

    /**
     * Používateľ, ktorý položku priradil
     */
    public function assignedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_by');
    }

    /**
     * Scope pre položky konkrétnej komisie
     */
    public function scopeForCommission($query, $commissionId)
    {
        return $query->where('commission_id', $commissionId);
    }

    /**
     * Scope pre položky s konkrétnym statusom
     */
    public function scopeByAssignmentStatus($query, $status)
    {
        return $query->where('assignment_status', $status);
    }

    /**
     * Scope pre nepriradené položky
     */
    public function scopeUnassigned($query)
    {
        return $query->where('assignment_status', self::ASSIGNMENT_UNASSIGNED)
                    ->orWhereNull('commission_id');
    }

    /**
     * Scope pre dokončené položky
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('assignment_status', [
            self::ASSIGNMENT_COMPLETED,
            self::ASSIGNMENT_VERIFIED
        ]);
    }

    /**
     * Získanie label pre assignment status
     */
    public function getAssignmentStatusLabelAttribute()
    {
        $statuses = self::getAssignmentStatusOptions();
        return $statuses[$this->assignment_status] ?? 'Neznámy stav';
    }

    /**
     * Priradenie položky k komisii
     */
    public function assignToCommission($commissionId, $userId = null)
    {
        $this->update([
            'commission_id' => $commissionId,
            'assignment_status' => self::ASSIGNMENT_ASSIGNED,
            'assigned_at' => now(),
            'assigned_by' => $userId ?? auth()->id()
        ]);
    }

    /**
     * Začatie inventarizácie položky
     */
    public function startInventory($userId = null)
    {
        $this->update([
            'assignment_status' => self::ASSIGNMENT_IN_PROGRESS,
            'started_at' => now()
        ]);

        // Záznam digitálnej úpravy
        $this->addDigitalUpdate('started', $userId ?? auth()->id());
    }

    /**
     * Dokončenie inventarizácie položky
     */
    public function completeInventory($userId = null, $notes = null)
    {
        $this->update([
            'assignment_status' => self::ASSIGNMENT_COMPLETED,
            'completed_at' => now(),
            'inventory_notes' => $notes
        ]);

        // Záznam digitálnej úpravy
        $this->addDigitalUpdate('completed', $userId ?? auth()->id(), $notes);
    }

    /**
     * Overenie inventarizácie položky
     */
    public function verifyInventory($userId = null)
    {
        $this->update([
            'assignment_status' => self::ASSIGNMENT_VERIFIED
        ]);

        // Záznam digitálnej úpravy
        $this->addDigitalUpdate('verified', $userId ?? auth()->id());
    }

    /**
     * Pridanie digitálnej úpravy
     */
    public function addDigitalUpdate($action, $userId, $notes = null)
    {
        $updates = $this->digital_updates ?? [];
        $updates[] = [
            'action' => $action,
            'user_id' => $userId,
            'timestamp' => now()->toISOString(),
            'notes' => $notes
        ];

        $this->update(['digital_updates' => $updates]);
    }

    /**
     * Kontrola, či je položka priradená používateľovi
     */
    public function isAssignedToUser($userId)
    {
        return $this->commission && $this->commission->hasMember($userId);
    }

    /**
     * Kontrola, či používateľ môže upravovať položku
     */
    public function canBeEditedByUser($userId)
    {
        // Administrátor môže upravovať všetko
        $user = \App\Models\User::find($userId);
        if ($user && $user->isAdmin()) {
            return true;
        }

        // Členovia komisie môžu upravovať priradené položky
        return $this->isAssignedToUser($userId);
    }

    /**
     * Alias pre plan() relationship pre konzistentnosť
     */
    public function inventoryPlan()
    {
        return $this->plan();
    }
}
