<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionIdToInventoryPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('commission_id')->nullable()->after('responsible_person_id');
            
            // Foreign key constraint
            $table->foreign('commission_id')->references('id')->on('inventory_commissions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            $table->dropForeign(['commission_id']);
            $table->dropColumn('commission_id');
        });
    }
}
