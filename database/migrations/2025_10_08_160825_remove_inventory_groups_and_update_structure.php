<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RemoveInventoryGroupsAndUpdateStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Najprv upravíme inventory_plan_items tabuľku
        Schema::table('inventory_plan_items', function (Blueprint $table) {
            // Pridáme commission_id stĺpec ak neexistuje
            if (!Schema::hasColumn('inventory_plan_items', 'commission_id')) {
                $table->foreignId('commission_id')->nullable()->after('expected_qty')->constrained('inventory_commissions')->nullOnDelete();
            }
            
            // Pridáme ďalšie stĺpce ak neexistujú
            if (!Schema::hasColumn('inventory_plan_items', 'assignment_status')) {
                $table->string('assignment_status')->default('unassigned')->after('commission_id');
            }
            if (!Schema::hasColumn('inventory_plan_items', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assignment_status');
            }
            if (!Schema::hasColumn('inventory_plan_items', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('assigned_at');
            }
            if (!Schema::hasColumn('inventory_plan_items', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('inventory_plan_items', 'assigned_by')) {
                $table->foreignId('assigned_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_plan_items', 'inventory_notes')) {
                $table->text('inventory_notes')->nullable()->after('assigned_by');
            }
            if (!Schema::hasColumn('inventory_plan_items', 'digital_updates')) {
                $table->json('digital_updates')->nullable()->after('inventory_notes');
            }
        });

        // 2. Presunieme dáta z inventory_groups do inventory_plan_items (ak existujú)
        if (Schema::hasTable('inventory_groups') && Schema::hasColumn('inventory_plan_items', 'inventory_group_id')) {
            // Aktualizujeme commission_id na základe inventory_group_id
            DB::statement('
                UPDATE inventory_plan_items 
                SET commission_id = (
                    SELECT commission_id 
                    FROM inventory_groups 
                    WHERE inventory_groups.id = inventory_plan_items.inventory_group_id
                )
                WHERE inventory_group_id IS NOT NULL
            ');
        }

        // 3. Odstránime inventory_group_id stĺpec z inventory_plan_items
        Schema::table('inventory_plan_items', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_plan_items', 'inventory_group_id')) {
                $table->dropForeign(['inventory_group_id']);
                $table->dropColumn('inventory_group_id');
            }
        });

        // 4. Odstránime všetky foreign key constraints pre inventory_groups
        if (Schema::hasTable('inventory_group_members')) {
            Schema::table('inventory_group_members', function (Blueprint $table) {
                $table->dropForeign(['inventory_group_id']);
                $table->dropForeign(['user_id']);
            });
            Schema::dropIfExists('inventory_group_members');
        }

        // 5. Odstránime inventory_groups tabuľku
        if (Schema::hasTable('inventory_groups')) {
            Schema::dropIfExists('inventory_groups');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recreate inventory_groups table
        Schema::create('inventory_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('commission_id')->constrained('inventory_commissions')->onDelete('cascade');
            $table->foreignId('inventory_plan_id')->constrained('inventory_plans')->onDelete('cascade');
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Add inventory_group_id back to inventory_plan_items
        Schema::table('inventory_plan_items', function (Blueprint $table) {
            $table->foreignId('inventory_group_id')->nullable()->after('expected_qty')->constrained('inventory_groups')->nullOnDelete();
        });

        // Remove commission-related columns from inventory_plan_items
        Schema::table('inventory_plan_items', function (Blueprint $table) {
            $table->dropForeign(['commission_id']);
            $table->dropColumn([
                'commission_id', 
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
