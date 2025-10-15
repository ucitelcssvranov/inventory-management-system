<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Iba admin s oprávneniami alebo inventory manager môže vytvárať plány
        return auth()->user()->hasAdminPrivileges() || auth()->user()->isInventoryManager();
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
            'date_start' => 'required|date|after_or_equal:today',
            'date_end' => 'required|date|after_or_equal:date_start',
            'inventory_day' => 'required|date|after_or_equal:date_start|before_or_equal:date_end',
            'unit_name' => 'required|string|max:255',
            'unit_address' => 'required|string|max:255',
            'planned_date' => 'nullable|date',
            'responsible_person_id' => 'required|exists:users,id',
            'commission_id' => 'required|exists:inventory_commissions,id',
            'plan_category_id' => 'nullable|exists:categories,id',
            'location_ids' => 'required|array|min:1',
            'location_ids.*' => 'exists:locations,id',
            'category_id' => 'nullable|exists:categories,id',
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
            'date_start.after_or_equal' => 'Dátum začiatku nemôže byť v minulosti.',
            'date_end.after_or_equal' => 'Dátum ukončenia musí byť po dátume začiatku.',
            'inventory_day.after_or_equal' => 'Deň inventúry musí byť medzi dátumom začiatku a ukončenia.',
            'inventory_day.before_or_equal' => 'Deň inventúry musí byť medzi dátumom začiatku a ukončenia.',
            'type.in' => 'Typ inventúry musí byť jeden z: fyzická, dokladová, kombinovaná.',
        ];
    }
}
