<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SystemSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->except(['show']);
    }

    /**
     * Display settings overview (for non-admin users - public settings only)
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function show()
    {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('settings.index');
        }

        $settings = SystemSetting::getPublicSettings();
        
        return view('settings.show', compact('settings'));
    }

    /**
     * Display a listing of all settings (admin only)
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $settings = SystemSetting::getAllGrouped();
        
        return view('settings.index', compact('settings'));
    }

    /**
     * Show the form for editing settings
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit()
    {
        $settings = SystemSetting::editable()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');
        
        return view('settings.edit', compact('settings'));
    }

    /**
     * Update the specified settings in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $settings = SystemSetting::editable()->get();
            
            // Build validation rules and custom attribute names
            $rules = [];
            $attributes = [];
            
            foreach ($settings as $setting) {
                $fieldName = "settings.{$setting->key}";
                
                if ($setting->validation_rules) {
                    $rules[$fieldName] = $setting->validation_rules;
                }
                
                $attributes[$fieldName] = $setting->label;
            }
            
            // Validate all settings at once
            $request->validate($rules, [], $attributes);

            // Update each setting
            foreach ($settings as $setting) {
                $value = $request->input("settings.{$setting->key}");
                
                // Handle boolean values from checkboxes
                if ($setting->type === 'boolean') {
                    $value = $request->has("settings.{$setting->key}") ? '1' : '0';
                }

                // Skip if no value provided and it's not a boolean
                if ($value === null && $setting->type !== 'boolean') {
                    continue;
                }

                // Update the setting
                $setting->update(['value' => $value]);
            }

            DB::commit();

            // Clear all settings cache
            SystemSetting::clearCache();

            return redirect()->route('settings.index')
                ->with('success', 'Nastavenia boli úspešne uložené.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Niektoré nastavenia neboli uložené kvôli chybám.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Chyba pri ukladaní nastavení: ' . $e->getMessage());
        }
    }

    /**
     * Update a single setting via AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSingle(Request $request, string $key)
    {
        $setting = SystemSetting::where('key', $key)->editable()->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Nastavenie nebolo nájdené alebo nie je editovateľné.'
            ], 404);
        }

        $value = $request->input('value');

        // Handle boolean values
        if ($setting->type === 'boolean') {
            $value = $request->boolean('value') ? '1' : '0';
        }

        // Validate the value
        if ($setting->validation_rules) {
            $validator = Validator::make(
                ['value' => $value],
                ['value' => $setting->validation_rules]
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first('value')
                ], 422);
            }
        }

        try {
            $setting->update(['value' => $value]);

            return response()->json([
                'success' => true,
                'message' => 'Nastavenie bolo úspešne uložené.',
                'value' => $setting->fresh()->formatted_value
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri ukladaní nastavenia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system information
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_type' => config('database.default'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];

        return view('settings.system-info', compact('info'));
    }

    /**
     * Clear system cache
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        try {
            // Clear application cache
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            // Clear settings cache
            SystemSetting::clearCache();

            return redirect()->back()
                ->with('success', 'Cache bol úspešne vymazaný.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Chyba pri mazaní cache: ' . $e->getMessage());
        }
    }

    /**
     * Export settings as JSON
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function export()
    {
        $settings = SystemSetting::select('key', 'value', 'type', 'group')
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($setting) {
                return [
                    'key' => $setting->key,
                    'value' => SystemSetting::castValue($setting->value, $setting->type),
                    'type' => $setting->type,
                    'group' => $setting->group
                ];
            });

        return response()->json([
            'exported_at' => now()->toISOString(),
            'settings' => $settings
        ]);
    }

    /**
     * Reset settings to default values
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'confirm_reset' => 'required|in:RESET'
        ], [
            'confirm_reset.required' => 'Potvrďte reset zadaním slova RESET.',
            'confirm_reset.in' => 'Pre potvrdenie resetu zadajte presne slovo RESET.'
        ]);

        try {
            // This would require having default values stored somewhere
            // For now, we'll just show a message
            return redirect()->back()
                ->with('info', 'Reset nastavení nie je v tejto verzii implementovaný. Kontaktujte administrátora.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Chyba pri resete nastavení: ' . $e->getMessage());
        }
    }
}