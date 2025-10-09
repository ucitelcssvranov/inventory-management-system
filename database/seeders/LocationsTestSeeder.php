<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationsTestSeeder extends Seeder
{
    /**
     * Vytvorí ukážkové lokácie pre testovanie
     */
    public function run()
    {
        // Vytvoriť ukážkové budovy
        $hlavnaBudova = Location::create([
            'name' => 'Hlavná budova',
            'type' => 'budova',
            'description' => 'Hlavná školská budova s kanceláriami a učebňami',
            'created_by' => 1,
        ]);

        $telLocvicna = Location::create([
            'name' => 'Telocvičňa',
            'type' => 'budova', 
            'description' => 'Budova telocvične a športových aktivít',
            'created_by' => 1,
        ]);

        // Vytvoriť miestnosti v hlavnej budove
        Location::create([
            'parent_id' => $hlavnaBudova->id,
            'type' => 'miestnost',
            'room_number' => '101',
            'room_description' => 'Riaditeľova kancelária',
            'created_by' => 1,
        ]);

        Location::create([
            'parent_id' => $hlavnaBudova->id,
            'type' => 'miestnost',
            'room_number' => '102',
            'room_description' => 'Učebňa informatiky',
            'created_by' => 1,
        ]);

        Location::create([
            'parent_id' => $hlavnaBudova->id,
            'type' => 'miestnost',
            'room_number' => '103',
            'room_description' => 'Učebňa matematiky',
            'created_by' => 1,
        ]);

        Location::create([
            'parent_id' => $hlavnaBudova->id,
            'type' => 'miestnost',
            'room_number' => 'A-12',
            'room_description' => 'Sklad učebných pomôcok',
            'created_by' => 1,
        ]);

        // Vytvoriť miestnosti v telocvični
        Location::create([
            'parent_id' => $telLocvicna->id,
            'type' => 'miestnost',
            'room_number' => 'T01',
            'room_description' => 'Hlavná telocvičňa',
            'created_by' => 1,
        ]);

        Location::create([
            'parent_id' => $telLocvicna->id,
            'type' => 'miestnost',
            'room_number' => 'T02',
            'room_description' => 'Šatňa chlapcov',
            'created_by' => 1,
        ]);

        Location::create([
            'parent_id' => $telLocvicna->id,
            'type' => 'miestnost',
            'room_number' => 'T03',
            'room_description' => 'Šatňa dievčat',
            'created_by' => 1,
        ]);
    }
}