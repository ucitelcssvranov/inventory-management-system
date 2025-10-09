<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePksToInventoryCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Premenuje hlavnú tabuľku ak existuje
        if (Schema::hasTable('pks')) {
            Schema::rename('pks', 'inventory_commissions');
        }
        
        // Premenuje pivot tabuľku ak existuje
        if (Schema::hasTable('pk_teacher')) {
            Schema::rename('pk_teacher', 'inventory_commission_teacher');
        }
        
        // Aktualizuje názov stĺpca v pivot tabuľke ak existuje
        if (Schema::hasTable('inventory_commission_teacher') && Schema::hasColumn('inventory_commission_teacher', 'pk_id')) {
            Schema::table('inventory_commission_teacher', function (Blueprint $table) {
                $table->renameColumn('pk_id', 'inventory_commission_id');
            });
        }
        
        // Aktualizuje foreign key v assets tabuľke ak existuje
        if (Schema::hasColumn('assets', 'pk_owner_id')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->renameColumn('pk_owner_id', 'inventory_commission_owner_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Vráti zmeny v obrátenom poradí
        if (Schema::hasColumn('assets', 'inventory_commission_owner_id')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->renameColumn('inventory_commission_owner_id', 'pk_owner_id');
            });
        }
        
        Schema::table('inventory_commission_teacher', function (Blueprint $table) {
            $table->renameColumn('inventory_commission_id', 'pk_id');
        });
        
        Schema::rename('inventory_commission_teacher', 'pk_teacher');
        Schema::rename('inventory_commissions', 'pks');
    }
}
