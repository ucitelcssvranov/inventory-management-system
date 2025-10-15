<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // add role to fillable
        'microsoft_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Konštanty pre role
    const ROLE_ADMIN = 'admin';
    const ROLE_INVENTORY_MANAGER = 'inventory_manager';
    const ROLE_COMMISSION_CHAIRMAN = 'commission_chairman';
    const ROLE_COMMISSION_MEMBER = 'commission_member';
    const ROLE_GROUP_LEADER = 'group_leader';
    const ROLE_INVENTORISATOR = 'inventorisator';

    public function isSpravca()
    {
        return $this->role === 'spravca';
    }

    public function isUcitel()
    {
        return $this->role === 'ucitel';
    }

    /**
     * Kontrola, či je používateľ administrátor
     */
    public function isAdmin()
    {
        return in_array($this->role, [self::ROLE_ADMIN, 'spravca']);
    }

    /**
     * Kontrola, či je admin v user mode (behá ako bežný používateľ)
     */
    public function isInUserMode()
    {
        return session('admin_user_mode', false) === true;
    }

    /**
     * Kontrola, či má admin oprávnenia (nie je v user mode)
     */
    public function hasAdminPrivileges()
    {
        return $this->isAdmin() && !$this->isInUserMode();
    }

    /**
     * Kontrola, či je používateľ správca inventarizácie
     */
    public function isInventoryManager()
    {
        return $this->role === self::ROLE_INVENTORY_MANAGER || $this->hasAdminPrivileges();
    }

    /**
     * Kontrola, či je používateľ predseda nejakej komisie
     */
    public function isAnyCommissionChairman()
    {
        return $this->chairmanCommissions()->exists();
    }

    /**
     * Kontrola, či je používateľ vedúci nejakej inventarizačnej skupiny
     */
    public function isAnyGroupLeader()
    {
        return false; // Skupiny už neexistujú
    }

    /**
     * Získanie možných rolí
     */
    public static function getRoleOptions()
    {
        return [
            self::ROLE_ADMIN => 'Administrátor',
            self::ROLE_INVENTORY_MANAGER => 'Správca inventarizácie',
            self::ROLE_COMMISSION_CHAIRMAN => 'Predseda komisie',
            self::ROLE_COMMISSION_MEMBER => 'Člen komisie',
            self::ROLE_GROUP_LEADER => 'Vedúci inventarizačnej skupiny',
            self::ROLE_INVENTORISATOR => 'Inventarizátor',
            'spravca' => 'Správca (legacy)',
            'ucitel' => 'Učiteľ (legacy)'
        ];
    }

    /**
     * Získanie názvu role
     */
    public function getRoleLabelAttribute()
    {
        $roles = self::getRoleOptions();
        return $roles[$this->role] ?? 'Neznáma rola';
    }

    /**
     * Inventarizačné komisie, kde je tento používateľ predsedom
     */
    public function chairmanCommissions()
    {
        return $this->hasMany(InventoryCommission::class, 'chairman_id');
    }

    /**
     * Inventarizačné komisie, kde je tento používateľ členom (okrem predsedu)
     */
    public function memberCommissions()
    {
        return $this->belongsToMany(InventoryCommission::class, 'inventory_commission_members', 'user_id', 'commission_id')
                    ->withTimestamps();
    }

    /**
     * Všetky inventarizačné komisie, kde je tento používateľ buď predsedom alebo členom
     */
    public function allCommissions()
    {
        $chairmanCommissions = $this->chairmanCommissions;
        $memberCommissions = $this->memberCommissions;
        
        return $chairmanCommissions->merge($memberCommissions)->unique('id')->sortBy('name');
    }

    /**
     * Kontrola, či je používateľ členom konkrétnej komisie
     */
    public function isCommissionMember($commissionId)
    {
        return $this->chairmanCommissions()->where('id', $commissionId)->exists() ||
               $this->memberCommissions()->where('inventory_commissions.id', $commissionId)->exists();
    }

    /**
     * Kontrola, či je používateľ predsedom konkrétnej komisie
     */
    public function isCommissionChairman($commissionId)
    {
        return $this->chairmanCommissions()->where('id', $commissionId)->exists();
    }

    /**
     * Lokácie vytvorené týmto používateľom
     */
    public function createdLocations()
    {
        return $this->hasMany(Location::class, 'created_by');
    }

    /**
     * Lokácie upravené týmto používateľom
     */
    public function updatedLocations()
    {
        return $this->hasMany(Location::class, 'updated_by');
    }

    /**
     * Inventarizačné komisie vytvorené týmto používateľom
     */
    public function createdCommissions()
    {
        return $this->hasMany(InventoryCommission::class, 'created_by');
    }

    /**
     * Inventarizačné komisie upravené týmto používateľom
     */
    public function updatedCommissions()
    {
        return $this->hasMany(InventoryCommission::class, 'updated_by');
    }

    /**
     * Alias pre memberCommissions() - používané v dashboard
     */
    public function memberOfCommissions()
    {
        return $this->memberCommissions();
    }

    /**
     * Alias pre chairmanCommissions() - používané v dashboard
     */
    public function chairedCommissions()
    {
        return $this->chairmanCommissions();
    }

    /**
     * Inventarizačné položky pridelené tomuto používateľovi cez komisie
     */
    public function assignedPlanItems()
    {
        return InventoryPlanItem::whereHas('commission', function($query) {
            $query->where('chairman_id', $this->id)
                  ->orWhereHas('members', function($subQuery) {
                      $subQuery->where('user_id', $this->id);
                  });
        });
    }
}
