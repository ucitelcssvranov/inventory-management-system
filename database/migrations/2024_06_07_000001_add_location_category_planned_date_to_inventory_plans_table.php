<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationCategoryPlannedDateToInventoryPlansTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            // These columns are already included in the create_inventory_plans_table migration
            // Only add columns that are not already present
            if (!Schema::hasColumn('inventory_plans', 'planned_date')) {
                $table->date('planned_date')->nullable();
            }
            if (!Schema::hasColumn('inventory_plans', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable();
                $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_plans', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable();
                $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['planned_date', 'location_id', 'category_id']);
        });
    }
}
