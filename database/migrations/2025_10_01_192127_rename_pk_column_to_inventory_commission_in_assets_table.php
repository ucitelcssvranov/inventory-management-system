<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePkColumnToInventoryCommissionInAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('assets') && Schema::hasColumn('assets', 'pk')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->renameColumn('pk', 'inventory_commission');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('assets') && Schema::hasColumn('assets', 'inventory_commission')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->renameColumn('inventory_commission', 'pk');
            });
        }
    }
}
