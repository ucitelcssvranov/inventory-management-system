<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryPlan;
use App\Models\InventoryCommission;
use App\Models\Location;
use App\Models\Category;

class InventoryPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Získaj komisie
        $commissions = InventoryCommission::all();
        
        if ($commissions->count() === 0) {
            $this->command->info('Nie sú vytvorené žiadne komisie');
            return;
        }

        // Získaj lokácie a kategórie (ak existujú)
        $locations = Location::all();
        $categories = Category::all();

        // Vytvor inventarizačný plán pre hlavnú komisiu
        $plan1 = InventoryPlan::create([
            'name' => 'Ročná inventarizácia 2025',
            'description' => 'Kompletná inventarizácia všetkého majetku školy',
            'type' => 'kombinovaná',
            'date' => now(),
            'date_start' => now()->addDays(7),
            'date_end' => now()->addDays(30),
            'inventory_day' => now()->addDays(15),
            'unit_name' => 'Stredná priemyselná škola Vranov',
            'unit_address' => 'Vranov nad Topľou',
            'storage_place' => 'Centrálny sklad',
            'responsible_person_id' => 1,
            'location_id' => $locations->first()->id ?? null,
            'category_id' => $categories->first()->id ?? null,
            'commission_id' => $commissions->first()->id,
            'status' => 'assigned',
            'created_by' => 1,
        ]);

        // Vytvor plán pre učebne
        $plan2 = InventoryPlan::create([
            'name' => 'Inventarizácia učební',
            'description' => 'Inventarizácia všetkých učební a laboratórií',
            'type' => 'fyzická',
            'date' => now(),
            'date_start' => now()->addDays(5),
            'date_end' => now()->addDays(20),
            'inventory_day' => now()->addDays(12),
            'unit_name' => 'Stredná priemyselná škola Vranov',
            'unit_address' => 'Vranov nad Topľou',
            'storage_place' => 'Učebne',
            'responsible_person_id' => 1,
            'location_id' => $locations->skip(1)->first()->id ?? null,
            'category_id' => $categories->skip(1)->first()->id ?? null,
            'commission_id' => $commissions->skip(1)->first()->id ?? $commissions->first()->id,
            'status' => 'assigned',
            'created_by' => 1,
        ]);

        // Vytvor plán pre administratívu
        $plan3 = InventoryPlan::create([
            'name' => 'Inventarizácia administratívy',
            'description' => 'Inventarizácia kancelárií a administratívnych priestorov',
            'type' => 'dokladová',
            'date' => now(),
            'date_start' => now()->addDays(10),
            'date_end' => now()->addDays(25),
            'inventory_day' => now()->addDays(17),
            'unit_name' => 'Stredná priemyselná škola Vranov',
            'unit_address' => 'Vranov nad Topľou',
            'storage_place' => 'Administratívne priestory',
            'responsible_person_id' => 1,
            'location_id' => $locations->last()->id ?? null,
            'category_id' => $categories->last()->id ?? null,
            'commission_id' => $commissions->skip(2)->first()->id ?? $commissions->first()->id,
            'status' => 'assigned',
            'created_by' => 1,
        ]);

        $this->command->info('Vytvorené 3 inventarizačné plány');
    }
}