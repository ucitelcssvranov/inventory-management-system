<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateStartAndDateEndToInventoryPlansTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            // These columns are already included in the create_inventory_plans_table migration
            // Only add columns that are not already present
            if (!Schema::hasColumn('inventory_plans', 'date_start')) {
                $table->date('date_start')->nullable();
            }
            if (!Schema::hasColumn('inventory_plans', 'date_end')) {
                $table->date('date_end')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            $table->dropColumn(['date_start', 'date_end']);
        });
    }
}
