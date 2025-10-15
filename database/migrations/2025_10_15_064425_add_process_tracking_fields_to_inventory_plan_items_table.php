<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcessTrackingFieldsToInventoryPlanItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_plan_items', function (Blueprint $table) {
            // Pridanie polí pre sledovanie procesu inventarizácie
            $table->timestamp('verified_at')->nullable()->after('completed_at');
            $table->unsignedBigInteger('started_by')->nullable()->after('assigned_by');
            $table->unsignedBigInteger('completed_by')->nullable()->after('started_by');
            $table->unsignedBigInteger('verified_by')->nullable()->after('completed_by');
            
            // Pridanie foreign key constraints
            $table->foreign('started_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_plan_items', function (Blueprint $table) {
            // Dropnutie foreign key constraints
            $table->dropForeign(['started_by']);
            $table->dropForeign(['completed_by']);
            $table->dropForeign(['verified_by']);
            
            // Dropnutie stĺpcov
            $table->dropColumn([
                'verified_at',
                'started_by',
                'completed_by', 
                'verified_by'
            ]);
        });
    }
}
