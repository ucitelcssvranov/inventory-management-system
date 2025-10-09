<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_plan_commission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_plan_id')->constrained('inventory_plans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->text('signature')->nullable();
            $table->timestamps();
            
            $table->unique(['inventory_plan_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_plan_commission');
    }
};