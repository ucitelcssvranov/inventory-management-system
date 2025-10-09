<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_plan_item_id')->constrained('inventory_plan_items')->onDelete('cascade');
            $table->foreignId('counted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('counted_at');
            $table->integer('counted_qty');
            $table->text('note')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_counts');
    }
};