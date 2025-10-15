<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $inventoryPlan = $this->route('inventory_plan');
        
        // Admin s oprávneniami môže upravovať všetko, ostatní iba svoje plány
        return auth()->user()->hasAdminPrivileges() || 
               auth()->user()->isInventoryManager() ||
               $inventoryPlan->created_by === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'type' => 'required|string|in:fyzická,dokladová,kombinovaná',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'inventory_day' => 'required|date|after_or_equal:date_start|before_or_equal:date_end',
            'unit_name' => 'required|string|max:255',
            'unit_address' => 'required|string|max:255',
            'responsible_person_id' => 'required|exists:users,id',
            'plan_category_id' => 'nullable|exists:categories,id',
            'commission_id' => 'required|exists:inventory_commissions,id',
            'location_ids' => 'required|array|min:1',
            'location_ids.*' => 'exists:locations,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Názov plánu je povinný.',
            'date_end.after_or_equal' => 'Dátum ukončenia musí byť po dátume začiatku.',
            'inventory_day.after_or_equal' => 'Deň inventúry musí byť medzi dátumom začiatku a ukončenia.',
            'inventory_day.before_or_equal' => 'Deň inventúry musí byť medzi dátumom začiatku a ukončenia.',
            'type.in' => 'Typ inventúry musí byť jeden z: fyzická, dokladová, kombinovaná.',
            'commission_id.required' => 'Inventarizačná komisia je povinná.',
            'commission_id.exists' => 'Vybraná komisia neexistuje.',
            'location_ids.required' => 'Musíte vybrať aspoň jednu lokáciu.',
            'location_ids.min' => 'Musíte vybrať aspoň jednu lokáciu.',
            'location_ids.*.exists' => 'Jedna z vybraných lokácií neexistuje.',
        ];
    }
}
