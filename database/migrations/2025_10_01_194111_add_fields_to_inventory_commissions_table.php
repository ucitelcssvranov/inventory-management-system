<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToInventoryCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_commissions', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->unsignedBigInteger('chairman_id')->nullable()->after('description');
            $table->unsignedBigInteger('created_by')->nullable()->after('chairman_id');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            
            // Foreign key constraints
            $table->foreign('chairman_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_commissions', function (Blueprint $table) {
            $table->dropForeign(['chairman_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['description', 'chairman_id', 'created_by', 'updated_by']);
        });
    }
}
