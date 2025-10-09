<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToInventoryPlansTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            // These columns are already included in the create_inventory_plans_table migration
            // Only add columns that are not already present
            if (!Schema::hasColumn('inventory_plans', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('inventory_plans', 'date')) {
                $table->date('date')->nullable();
            }
            if (!Schema::hasColumn('inventory_plans', 'inventory_day')) {
                $table->date('inventory_day')->nullable();
            }
            if (!Schema::hasColumn('inventory_plans', 'unit_name')) {
                $table->string('unit_name')->nullable();
            }
            if (!Schema::hasColumn('inventory_plans', 'unit_address')) {
                $table->string('unit_address')->nullable();
            }
            if (!Schema::hasColumn('inventory_plans', 'storage_place')) {
                $table->string('storage_place')->nullable();
            }
            if (!Schema::hasColumn('inventory_plans', 'responsible_person_id')) {
                $table->unsignedBigInteger('responsible_person_id')->nullable();
                $table->foreign('responsible_person_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            $table->dropForeign(['responsible_person_id']);
            $table->dropColumn([
                'description',
                'date',
                'inventory_day',
                'unit_name',
                'unit_address',
                'storage_place',
                'responsible_person_id'
            ]);
        });
    }
}
