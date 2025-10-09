<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryCommission;
use App\Models\User;

class CommissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Získaj používateľov
        $users = User::all();
        
        if ($users->count() < 3) {
            $this->command->info('Nie je dostatok používateľov pre vytvorenie komisií');
            return;
        }

        // Vytvor hlavnú inventarizačnú komisiu
        $commission1 = InventoryCommission::create([
            'name' => 'Hlavná inventarizačná komisia',
            'description' => 'Komisia zodpovedná za inventarizáciu celej školy',
            'chairman_id' => $users->where('role', 'commission_chairman')->first()->id ?? $users[1]->id,
            'created_by' => 1,
        ]);

        // Pridaj členov do komisie
        $memberIds = $users->whereIn('role', ['commission_member', 'inventorisator'])->pluck('id')->take(3);
        if ($memberIds->count() === 0) {
            $memberIds = $users->skip(2)->take(3)->pluck('id');
        }
        $commission1->members()->attach($memberIds);

        // Vytvor komisiu pre učebne
        $commission2 = InventoryCommission::create([
            'name' => 'Komisia pre učebne',
            'description' => 'Komisia zodpovedná za inventarizáciu učební a laboratórií',
            'chairman_id' => $users->where('role', 'commission_chairman')->skip(1)->first()->id ?? $users[2]->id,
            'created_by' => 1,
        ]);

        // Pridaj rôznych členov do druhej komisie
        $memberIds2 = $users->whereIn('role', ['commission_member', 'group_leader'])->skip(2)->pluck('id')->take(2);
        if ($memberIds2->count() === 0) {
            $memberIds2 = $users->skip(5)->take(2)->pluck('id');
        }
        $commission2->members()->attach($memberIds2);

        // Vytvor komisiu pre administratívne priestory
        $commission3 = InventoryCommission::create([
            'name' => 'Komisia pre administratívu',
            'description' => 'Komisia zodpovedná za inventarizáciu kancelárií a administratívnych priestorov',
            'chairman_id' => $users->where('role', 'inventory_manager')->first()->id ?? $users[3]->id,
            'created_by' => 1,
        ]);

        // Pridaj členov do tretej komisie
        $memberIds3 = $users->whereIn('role', ['commission_member', 'inventorisator'])->skip(1)->pluck('id')->take(2);
        if ($memberIds3->count() === 0) {
            $memberIds3 = $users->skip(7)->take(2)->pluck('id');
        }
        $commission3->members()->attach($memberIds3);

        $this->command->info('Vytvorené 3 inventarizačné komisie s členmi');
    }
}
