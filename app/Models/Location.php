<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'type',
        'room_number',
        'room_description',
        'notes',
        'building',
        'room',
        'description',
        'created_by',
        'updated_by',
    ];

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function assets()
    {
        return $this->hasMany(\App\Models\Asset::class);
    }

    // Scope pre budovy (bez parent_id)
    public function scopeBudovy($query)
    {
        return $query->where('type', 'budova')->whereNull('parent_id');
    }

    // Scope pre miestnosti (s parent_id)
    public function scopeMiestnosti($query)
    {
        return $query->where('type', 'miestnost')->whereNotNull('parent_id');
    }

    // Getter pre celý názov miestnosti (budova + číslo + popis)
    public function getFullNameAttribute()
    {
        if ($this->type === 'miestnost' && $this->parent) {
            $parts = [$this->parent->name];
            if ($this->room_number) {
                $parts[] = $this->room_number;
            }
            if ($this->room_description) {
                $parts[] = "({$this->room_description})";
            }
            return implode(' - ', $parts);
        }
        return $this->name;
    }

    // Zistí, či je lokácia budova
    public function isBudova()
    {
        return $this->type === 'budova' && is_null($this->parent_id);
    }

    // Zistí, či je lokácia miestnosť
    public function isMiestnost()
    {
        return $this->type === 'miestnost' && !is_null($this->parent_id);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
