<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Názov skupiny (napr. "Skupina A - Učebne 1. poschodie")
            $table->text('description')->nullable(); // Popis skupiny a jej zodpovednosti
            $table->string('color_code', 7)->default('#3498db'); // Farebný kód pre rozlíšenie skupín
            
            // Vzťahy
            $table->unsignedBigInteger('commission_id'); // Príslušnosť ku komisii
            $table->unsignedBigInteger('leader_id'); // Vedúci skupiny
            $table->unsignedBigInteger('inventory_plan_id'); // Príslušnosť k inventarizačnému plánu
            
            // Stav a progress
            $table->enum('status', ['draft', 'assigned', 'in_progress', 'completed', 'approved'])
                  ->default('draft');
            $table->integer('total_items')->default(0); // Celkový počet položiek
            $table->integer('completed_items')->default(0); // Dokončené položky
            $table->decimal('progress_percentage', 5, 2)->default(0.00); // Percentuálny pokrok
            
            // Časové údaje
            $table->timestamp('assigned_at')->nullable(); // Kedy bola skupina priradená
            $table->timestamp('started_at')->nullable(); // Kedy začala inventarizácia
            $table->timestamp('completed_at')->nullable(); // Kedy bola dokončená
            $table->timestamp('approved_at')->nullable(); // Kedy bola schválená
            
            // Audit trail
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('commission_id')->references('id')->on('inventory_commissions')->onDelete('cascade');
            $table->foreign('leader_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('inventory_plan_id')->references('id')->on('inventory_plans')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
            
            // Indexes
            $table->index(['commission_id', 'status']);
            $table->index(['inventory_plan_id', 'status']);
            $table->index('leader_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_groups');
    }
}