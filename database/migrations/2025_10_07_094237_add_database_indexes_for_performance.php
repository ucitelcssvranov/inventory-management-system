<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatabaseIndexesForPerformance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Indexy pre inventory_plans
        Schema::table('inventory_plans', function (Blueprint $table) {
            if (!$this->hasIndex('inventory_plans', 'inventory_plans_status_index')) {
                $table->index('status');
            }
            if (!$this->hasIndex('inventory_plans', 'inventory_plans_created_by_index')) {
                $table->index('created_by');
            }
            if (!$this->hasIndex('inventory_plans', 'inventory_plans_commission_id_index')) {
                $table->index('commission_id');
            }
        });

        // Indexy pre inventory_groups  
        Schema::table('inventory_groups', function (Blueprint $table) {
            if (!$this->hasIndex('inventory_groups', 'inventory_groups_status_index')) {
                $table->index('status');
            }
            if (!$this->hasIndex('inventory_groups', 'inventory_groups_commission_id_index')) {
                $table->index('commission_id');
            }
            if (!$this->hasIndex('inventory_groups', 'inventory_groups_inventory_plan_id_index')) {
                $table->index('inventory_plan_id');
            }
        });

        // Indexy pre assets
        Schema::table('assets', function (Blueprint $table) {
            if (!$this->hasIndex('assets', 'assets_location_id_index')) {
                $table->index('location_id');
            }
            if (!$this->hasIndex('assets', 'assets_category_id_index')) {
                $table->index('category_id');
            }
        });

        // Indexy pre users
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_role_index')) {
                $table->index('role');
            }
        });
    }

    /**
     * Check if index exists
     */
    private function hasIndex($table, $name)
    {
        $indexes = \DB::select(\DB::raw('SHOW INDEX FROM ' . $table));
        foreach ($indexes as $index) {
            if ($index->Key_name === $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Pre jednoduchosť - indexy sa môžu ponechať, nemajú negatívny vplyv
        // V prípade potreby ich možno odstrániť manuálne
    }
}
