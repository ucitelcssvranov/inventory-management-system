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
        // Iba admin alebo inventory manager môže vytvárať plány
        return auth()->user()->isAdmin() || auth()->user()->isInventoryManager();
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
            'storage_place' => 'required|string|max:255',
            'responsible_person_id' => 'required|exists:users,id',
            'planned_date' => 'nullable|date',
            'location_id' => 'nullable|exists:locations,id',
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
