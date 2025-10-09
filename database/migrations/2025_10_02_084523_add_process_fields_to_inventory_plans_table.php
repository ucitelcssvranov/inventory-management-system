<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcessFieldsToInventoryPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            // Zmena status na varchar pre nové hodnoty
            $table->string('status')->default('draft')->change();
            
            // Pridanie nových polí pre proces inventarizácie
            $table->string('process_status')->nullable()->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('updated_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('started_at')->nullable()->after('approved_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->timestamp('signed_at')->nullable()->after('completed_at');
            
            // Foreign key constraint
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_plans', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'process_status',
                'approved_by',
                'approved_at',
                'started_at',
                'completed_at',
                'signed_at'
            ]);
        });
    }
}
