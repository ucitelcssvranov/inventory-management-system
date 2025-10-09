<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run()
    {
        // Vytvoríme ukážkové budovy
        $budovy = [
            [
                'name' => 'Hlavná budova',
                'type' => 'budova',
                'notes' => 'Hlavná budova školy s administratívnymi priestormi',
                'miestnosti' => [
                    ['number' => '101', 'description' => 'Riaditeľňa'],
                    ['number' => '102', 'description' => 'Sekretariát'],
                    ['number' => '103', 'description' => 'Učebňa matematiky'],
                    ['number' => '104', 'description' => 'Učebňa slovenčiny'],
                    ['number' => '105', 'description' => 'Učebňa angličtiny'],
                    ['number' => '201', 'description' => 'Počítačová učebňa'],
                    ['number' => '202', 'description' => 'Knižnica'],
                    ['number' => '203', 'description' => 'Učebňa histórie'],
                ]
            ],
            [
                'name' => 'Pavilón A',
                'type' => 'budova',
                'notes' => 'Pavilón pre prírodné vedy',
                'miestnosti' => [
                    ['number' => 'A01', 'description' => 'Laboratórium chémie'],
                    ['number' => 'A02', 'description' => 'Laboratórium fyziky'],
                    ['number' => 'A03', 'description' => 'Učebňa biológie'],
                    ['number' => 'A04', 'description' => 'Prípravňa'],
                ]
            ],
            [
                'name' => 'Pavilón B',
                'type' => 'budova', 
                'notes' => 'Športový pavilón',
                'miestnosti' => [
                    ['number' => 'B01', 'description' => 'Veľká telocvičňa'],
                    ['number' => 'B02', 'description' => 'Malá telocvičňa'],
                    ['number' => 'B03', 'description' => 'Šatňa chlapci'],
                    ['number' => 'B04', 'description' => 'Šatňa dievčatá'],
                    ['number' => 'B05', 'description' => 'Sklad športových potrieb'],
                ]
            ],
            [
                'name' => 'Jedáleň',
                'type' => 'budova',
                'notes' => 'Školská jedáleň a kuchyňa',
                'miestnosti' => [
                    ['number' => '001', 'description' => 'Jedálenská hala'],
                    ['number' => '002', 'description' => 'Kuchyňa'],
                    ['number' => '003', 'description' => 'Sklad potravín'],
                    ['number' => '004', 'description' => 'Umyvárňa riadu'],
                ]
            ]
        ];

        foreach ($budovy as $budovaData) {
            // Vytvoríme budovu
            $budova = Location::create([
                'name' => $budovaData['name'],
                'type' => $budovaData['type'],
                'notes' => $budovaData['notes'],
                'parent_id' => null,
                'created_by' => 1, // Predpokladáme, že existuje user s ID 1
            ]);

            // Vytvoríme miestnosti pre túto budovu
            foreach ($budovaData['miestnosti'] as $miestnostData) {
                Location::create([
                    'name' => $budova->name . ' - ' . $miestnostData['number'],
                    'type' => 'miestnost',
                    'parent_id' => $budova->id,
                    'room_number' => $miestnostData['number'],
                    'room_description' => $miestnostData['description'],
                    'created_by' => 1,
                ]);
            }
        }

        $this->command->info('Vytvorené ' . count($budovy) . ' budov s miestnosťami.');
    }
}