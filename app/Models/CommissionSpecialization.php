<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommissionSpecialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_id',
        'category_id',
        'experience_level',
        'notes'
    ];

    protected $casts = [
        'experience_level' => 'integer'
    ];

    /**
     * Vzťah ku komisii
     */
    public function commission()
    {
        return $this->belongsTo(InventoryCommission::class);
    }

    /**
     * Vzťah ku kategórii
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Získa label pre úroveň skúseností
     */
    public function getExperienceLevelLabelAttribute()
    {
        $levels = [
            1 => 'Začiatočník',
            2 => 'Základná úroveň',
            3 => 'Pokročilý',
            4 => 'Expert',
            5 => 'Špecialista'
        ];

        return $levels[$this->experience_level] ?? 'Neznáme';
    }

    /**
     * Scope pre filtrovanie podľa úrovne skúseností
     */
    public function scopeByExperienceLevel($query, $level)
    {
        return $query->where('experience_level', '>=', $level);
    }

    /**
     * Scope pre expertnú úroveň (4+)
     */
    public function scopeExpert($query)
    {
        return $query->where('experience_level', '>=', 4);
    }
}