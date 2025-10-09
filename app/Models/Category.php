<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name','code','depreciation_group','useful_life'];

    public function assets()
    {
        return $this->hasMany(\App\Models\Asset::class);
    }
}
