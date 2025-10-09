<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('pk_teacher', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pk_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('pk_id')->references('id')->on('pks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['pk_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pk_teacher');
        Schema::dropIfExists('pks');
    }
};
