<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'building')) {
                $table->string('building')->nullable();
            }
            if (!Schema::hasColumn('locations', 'room')) {
                $table->string('room', 100)->nullable();
            }
            if (!Schema::hasColumn('locations', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('locations', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users');
            }
            if (!Schema::hasColumn('locations', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('users');
            }
        });
    }

    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['building', 'room', 'description', 'created_by', 'updated_by']);
        });
    }
};
