<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Location;

class AddTelocvicnaRooms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Nájdeme ID budovy "Telocvičňa a spojovacia chodba"
        $building = Location::where('name', 'Telocvičňa a spojovacia chodba')->first();
        
        if (!$building) {
            throw new Exception('Budova "Telocvičňa a spojovacia chodba" nenájdená');
        }
        
        $rooms = [
            ['124', 'Technická učebňa'],
            ['125', 'Archív'],
            ['127', 'Učebňa jazykov'],
            ['134', 'Šatňa dievčatá'],
            ['138', 'Šatňa chlapci'],
            ['136', 'Sklad lyží'],
            ['137', 'Sklad pneumatík'],
            ['133', 'Kabinet TSV'],
            ['142', 'Sklad lôpt + posilňovňa'],
            ['143', 'Sklad športových potrieb']
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
        $building = Location::where('name', 'Telocvičňa a spojovacia chodba')->first();
        
        if ($building) {
            // Odstránime všetky miestnosti tejto budovy
            Location::where('parent_id', $building->id)->delete();
        }
    }
}
