<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvenMoreFieldsToInventoryPlansTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            // Pridaj ďalšie polia podľa potreby
        });
    }

    public function down()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            // Odstráň tieto polia
        });
    }
}
