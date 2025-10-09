<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryCommission extends Model
{
    use HasFactory;

    protected $table = 'inventory_commissions';

    protected $fillable = [
        'name', 
        'description', 
        'chairman_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Predseda komisie
     */
    public function chairman()
    {
        return $this->belongsTo(User::class, 'chairman_id');
    }

    /**
     * Členovia komisie (okrem predsedu)
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'inventory_commission_members', 'commission_id', 'user_id')
                    ->withTimestamps()
                    ->orderBy('users.name');
    }

    /**
     * Všetci členovia komisie vrátane predsedu
     */
    public function allMembers()
    {
        $chairman = $this->chairman ? collect([$this->chairman]) : collect();
        $members = $this->members;
        
        return $chairman->merge($members)->unique('id')->sortBy('name');
    }

    /**
     * Inventarizačné plány pridelené tejto komisii
     */
    public function inventoryPlans()
    {
        return $this->hasMany(InventoryPlan::class, 'commission_id');
    }

    /**
     * Inventarizačné skupiny v tejto komisii
     */
    public function inventoryGroups()
    {
        return $this->hasMany(\App\Models\InventoryGroup::class, 'commission_id');
    }

    /**
     * Používateľ, ktorý komisiu vytvoril
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Používateľ, ktorý komisiu naposledy upravil
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope pre aktívne komisie (s predsedom)
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('chairman_id');
    }

    /**
     * Kontrola, či používateľ je členom komisie (vrátane predsedu)
     */
    public function hasMember($userId)
    {
        return $this->chairman_id == $userId || $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Získa celkový počet členov komisie (predseda + členovia)
     */
    public function getTotalMembersCountAttribute()
    {
        $count = $this->members()->count();
        if ($this->chairman_id) {
            $count++;
        }
        return $count;
    }

    /**
     * Získa plný názov komisie s počtom členov
     */
    public function getFullNameAttribute()
    {
        $membersCount = $this->total_members_count;
        return "{$this->name} ({$membersCount} " . ($membersCount == 1 ? 'člen' : 'členov') . ")";
    }

    /**
     * Validačné pravidlá
     */
    public static function validationRules($id = null)
    {
        return [
            'name' => 'required|string|max:255|unique:inventory_commissions,name' . ($id ? ",{$id}" : ''),
            'description' => 'nullable|string|max:1000',
            'chairman_id' => 'required|exists:users,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id|different:chairman_id',
        ];
    }
}
