<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateInventoryCountsTableForNewStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_counts', function (Blueprint $table) {
            // Pridáme nové stĺpce pre API kompatibilitu
            if (!Schema::hasColumn('inventory_counts', 'asset_id')) {
                $table->foreignId('asset_id')->nullable()->after('inventory_plan_item_id')->constrained('assets')->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_counts', 'counted_quantity')) {
                $table->integer('counted_quantity')->nullable()->after('counted_qty');
            }
            if (!Schema::hasColumn('inventory_counts', 'condition')) {
                $table->enum('condition', ['new', 'good', 'fair', 'poor', 'damaged'])->nullable()->after('counted_quantity');
            }
            if (!Schema::hasColumn('inventory_counts', 'notes')) {
                $table->text('notes')->nullable()->after('condition');
            }
            if (!Schema::hasColumn('inventory_counts', 'location_found')) {
                $table->foreignId('location_found')->nullable()->after('notes')->constrained('locations')->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_counts', 'photo')) {
                $table->text('photo')->nullable()->after('location_found'); // Pre base64 fotografie
            }
            if (!Schema::hasColumn('inventory_counts', 'plan_item_id')) {
                $table->foreignId('plan_item_id')->nullable()->after('photo')->constrained('inventory_plan_items')->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_counts', 'expected_quantity')) {
                $table->integer('expected_quantity')->nullable()->after('plan_item_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_counts', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropForeign(['location_found']);
            $table->dropForeign(['plan_item_id']);
            $table->dropColumn([
                'asset_id',
                'counted_quantity',
                'condition',
                'notes',
                'location_found',
                'photo',
                'plan_item_id',
                'expected_quantity'
            ]);
        });
    }
}
