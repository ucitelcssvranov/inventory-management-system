<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryCommission;
use App\Models\User;

class InventoryCommissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Získaj používateľov (predpokladáme, že už existujú z UserSeeder)
        $users = User::all();
        
        if ($users->count() < 3) {
            $this->command->warn('Potrebuje minimálne 3 používateľov na vytvorenie komisií. Spustite UserSeeder najprv.');
            return;
        }

        // Komisie podľa predmetových oblastí
        $commissions = [
            [
                'name' => 'Inventarizačná komisia - Informatika',
                'description' => 'Zodpovedná za inventarizáciu počítačovej techniky, serverov, sieťových prvkov a softvéru.',
                'chairman_id' => $users->random()->id,
                'members_count' => 2
            ],
            [
                'name' => 'Inventarizačná komisia - Prírodovedné predmety',
                'description' => 'Zodpovedná za inventarizáciu laboratórnych prístrojov, chemikálií a učebných pomôcok pre prírodné vedy.',
                'chairman_id' => $users->random()->id,
                'members_count' => 3
            ],
            [
                'name' => 'Inventarizačná komisia - Telocvik a šport',
                'description' => 'Zodpovedná za inventarizáciu športových potrieb, náčinia a vybavenia telocviční.',
                'chairman_id' => $users->random()->id,
                'members_count' => 2
            ],
            [
                'name' => 'Inventarizačná komisia - Kancelárske vybavenie',
                'description' => 'Zodpovedná za inventarizáciu nábytku, kancelárskej techniky a administratívnych potrieb.',
                'chairman_id' => $users->random()->id,
                'members_count' => 2
            ],
            [
                'name' => 'Inventarizačná komisia - Knižnica a AV technika',
                'description' => 'Zodpovedná za inventarizáciu kníh, časopisov, audiovizuálnej techniky a didaktických pomôcok.',
                'chairman_id' => $users->random()->id,
                'members_count' => 1
            ]
        ];

        foreach ($commissions as $commissionData) {
            // Vytvor komisiu
            $commission = InventoryCommission::create([
                'name' => $commissionData['name'],
                'description' => $commissionData['description'],
                'chairman_id' => $commissionData['chairman_id'],
                'created_by' => $users->first()->id, // Prvý používateľ ako vytvoriteľ
            ]);

            // Pridaj členov (okrem predsedu)
            $availableUsers = $users->where('id', '!=', $commissionData['chairman_id']);
            $members = $availableUsers->random(min($commissionData['members_count'], $availableUsers->count()));
            
            if ($members->count() > 0) {
                $commission->members()->attach($members->pluck('id'));
            }

            $this->command->info("Vytvorená komisia: {$commission->name} (Predseda: {$commission->chairman->name}, Členov: {$commission->members->count()})");
        }

        $this->command->info('Inventarizačné komisie boli úspešne vytvorené.');
    }
}
