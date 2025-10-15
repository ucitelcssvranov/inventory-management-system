<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryPlanLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_plan_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_plan_id')->constrained('inventory_plans')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->timestamps();
            
            // Zabezpečíme, aby sa každá kombinácia plánu a lokácie nevyskytovala viac krát
            $table->unique(['inventory_plan_id', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_plan_locations');
    }
}
