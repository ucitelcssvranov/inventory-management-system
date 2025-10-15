<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public',
        'is_editable',
        'validation_rules',
        'options',
        'sort_order'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_editable' => 'boolean',
        'validation_rules' => 'array',
        'options' => 'array',
        'sort_order' => 'integer'
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "system_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, $value): bool
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }
        
        $setting->update(['value' => $value]);
        
        // Clear cache
        Cache::forget("system_setting_{$key}");
        
        return true;
    }

    /**
     * Get all settings grouped by group
     */
    public static function getAllGrouped(): array
    {
        $settings = static::orderBy('group')->orderBy('sort_order')->get();
        
        return $settings->groupBy('group')->map(function ($groupSettings) {
            return $groupSettings->map(function ($setting) {
                $setting->cast_value = static::castValue($setting->value, $setting->type);
                return $setting;
            });
        })->toArray();
    }

    /**
     * Get settings that are public (visible to non-admin users)
     */
    public static function getPublicSettings(): array
    {
        return static::where('is_public', true)
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($setting) {
                $setting->cast_value = static::castValue($setting->value, $setting->type);
                return $setting;
            })
            ->groupBy('group')
            ->toArray();
    }

    /**
     * Cast setting value to appropriate type
     */
    protected static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            case 'text':
            case 'string':
            case 'select':
            default:
                return $value;
        }
    }

    /**
     * Get form input type for setting type
     */
    public function getInputTypeAttribute(): string
    {
        switch ($this->type) {
            case 'boolean':
                return 'checkbox';
            case 'integer':
                return 'number';
            case 'text':
                return 'textarea';
            case 'select':
                return 'select';
            case 'json':
                return 'textarea';
            case 'string':
            default:
                return 'text';
        }
    }

    /**
     * Get formatted value for display
     */
    public function getFormattedValueAttribute(): string
    {
        switch ($this->type) {
            case 'boolean':
                return $this->value ? 'Áno' : 'Nie';
            case 'json':
                return json_encode(json_decode($this->value), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            default:
                return $this->value ?? '';
        }
    }

    /**
     * Scope for editable settings
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    /**
     * Scope for public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for settings by group
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::select('key')->get();
        
        foreach ($settings as $setting) {
            Cache::forget("system_setting_{$setting->key}");
        }
    }

    /**
     * Boot method to clear cache when setting is updated
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($setting) {
            Cache::forget("system_setting_{$setting->key}");
        });

        static::created(function ($setting) {
            Cache::forget("system_setting_{$setting->key}");
        });

        static::deleted(function ($setting) {
            Cache::forget("system_setting_{$setting->key}");
        });
    }
}