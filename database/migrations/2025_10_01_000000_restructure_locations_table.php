<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Upraví štruktúru lokácií na:
     * - Budovy (bez parent_id, type='budova')
     * - Miestnosti (s parent_id na budovu, type='miestnost', room_number, room_description)
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            // Pridáme nové stĺpce pre lepšiu štruktúru
            if (!Schema::hasColumn('locations', 'room_number')) {
                $table->string('room_number', 50)->nullable()->after('name');
            }
            if (!Schema::hasColumn('locations', 'room_description')) {
                $table->string('room_description')->nullable()->after('room_number');
            }
            
            // Type stĺpec už existuje ako string, zmeníme ho na enum s pomocou raw SQL
            // pretože Laravel má problémy s change() na enum
        });

        // Použijeme raw SQL na zmenu type stĺpca na enum
        DB::statement("ALTER TABLE locations MODIFY COLUMN type ENUM('budova', 'miestnost') DEFAULT 'budova'");
        
        Schema::table('locations', function (Blueprint $table) {
            // Pridáme indexy pre lepšiu výkonnosť
            $table->index(['type', 'parent_id'], 'idx_locations_type_parent');
            $table->index(['room_number', 'parent_id'], 'idx_locations_room_parent');
        });
    }

    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            // Odstránime indexy
            $table->dropIndex('idx_locations_type_parent');
            $table->dropIndex('idx_locations_room_parent');
            
            // Odstránime nové stĺpce
            $table->dropColumn(['room_number', 'room_description']);
        });
        
        // Vrátime type stĺpec na string
        DB::statement("ALTER TABLE locations MODIFY COLUMN type VARCHAR(255) NULL");
    }
};