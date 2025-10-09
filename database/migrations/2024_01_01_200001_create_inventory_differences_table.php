<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_differences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_plan_id');
            $table->unsignedBigInteger('asset_id');
            $table->decimal('accounting_value', 12, 2);
            $table->decimal('real_value', 12, 2);
            $table->decimal('difference', 12, 2);
            $table->string('reason')->nullable();
            $table->string('settlement_proposal')->nullable();
            $table->unsignedBigInteger('responsible_user_id')->nullable();
            $table->timestamps();

            $table->foreign('inventory_plan_id')->references('id')->on('inventory_plans')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('responsible_user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_differences');
    }
};
