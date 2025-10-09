<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInventoryGroupToInventoryPlanItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_plan_items', function (Blueprint $table) {
            // Pridať stĺpec pre pridelenie položky k inventarizačnej skupine
            $table->unsignedBigInteger('inventory_group_id')->nullable()->after('expected_qty');
            
            // Pridať dodatočné polia pre lepšie sledovanie
            $table->enum('assignment_status', ['unassigned', 'assigned', 'in_progress', 'completed', 'verified'])
                  ->default('unassigned')->after('inventory_group_id');
            $table->timestamp('assigned_at')->nullable()->after('assignment_status');
            $table->timestamp('started_at')->nullable()->after('assigned_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->unsignedBigInteger('assigned_by')->nullable()->after('completed_at');
            
            // Notes a poznámky pre inventarizátorov
            $table->text('inventory_notes')->nullable()->after('assigned_by');
            $table->json('digital_updates')->nullable()->after('inventory_notes'); // Pre sledovanie digitálnych úprav
            
            // Foreign key constraint
            $table->foreign('inventory_group_id')->references('id')->on('inventory_groups')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            
            // Index pre výkon
            $table->index(['inventory_group_id', 'assignment_status']);
            $table->index('assignment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_plan_items', function (Blueprint $table) {
            $table->dropForeign(['inventory_group_id']);
            $table->dropForeign(['assigned_by']);
            $table->dropIndex(['inventory_group_id', 'assignment_status']);
            $table->dropIndex(['assignment_status']);
            
            $table->dropColumn([
                'inventory_group_id',
                'assignment_status',
                'assigned_at',
                'started_at', 
                'completed_at',
                'assigned_by',
                'inventory_notes',
                'digital_updates'
            ]);
        });
    }
}