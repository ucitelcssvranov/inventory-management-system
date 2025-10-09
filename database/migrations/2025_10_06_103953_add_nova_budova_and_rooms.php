<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Location;

class AddNovaBudovaAndRooms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Vytvoríme novú budovu "Nova budova"
        $building = Location::create([
            'name' => 'Nova budova',
            'type' => 'budova',
            'parent_id' => null
        ]);
        
        $rooms = [
            ['122', 'Sklad pod schodiskom'],
            ['119', 'Výchovný poradca'],
            ['118', 'Jedáleň učitelia'],
            ['120', 'Jedáleň'],
            ['121', 'Mediálna učebňa'],
            ['215', 'Kabinet'],
            ['216', 'Trieda'],
            ['217', 'Trieda'],
            ['218', 'Trieda'],
            ['302', 'Kabinet INF'],
            ['303', 'INF1'],
            ['304', 'INF2'],
            ['305', 'Trieda']
        ];

        foreach ($rooms as $room) {
            Location::create([
                'name' => $room[0] . ' ' . $room[1],
                'type' => 'miestnost',
                'parent_id' => $building->id,
                'room_number' => $room[0],
                'room_description' => $room[1]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nájdeme budovu
        $building = Location::where('name', 'Nova budova')->first();
        
        if ($building) {
            // Odstránime všetky miestnosti tejto budovy
            Location::where('parent_id', $building->id)->delete();
            // Odstránime budovu
            $building->delete();
        }
    }
}
