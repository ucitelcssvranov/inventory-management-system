<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTypeEnumInInventoryPlansTable extends Migration
{
    public function up()
    {
        // The type column is already defined correctly in the create_inventory_plans_table migration
        // This migration is no longer needed
    }

    public function down()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('inventory_plans', function (Blueprint $table) {
            $table->enum('type', ['physical','documentary','combined'])->default('physical')->after('date_to');
        });
    }
}
