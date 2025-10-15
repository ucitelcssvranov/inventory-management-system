<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionSpecializationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_id')->constrained('inventory_commissions')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->integer('experience_level')->default(1); // 1-5 skala
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['commission_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commission_specializations');
    }
}
