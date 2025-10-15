<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, text
            $table->string('group')->default('general'); // general, inventory, export, notifications, security
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // whether non-admin users can see this setting
            $table->boolean('is_editable')->default(true); // whether this setting can be edited via UI
            $table->json('validation_rules')->nullable(); // JSON array of Laravel validation rules
            $table->json('options')->nullable(); // For select/radio options
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default settings one by one to avoid column order issues
        $settings = [
            [
                'key' => 'system_name',
                'value' => 'Inventarizačný systém CSŠ Vranov',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Názov systému',
                'description' => 'Názov systému zobrazovaný v hlavičke a dokumentoch',
                'is_public' => true,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'string', 'max:255']),
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_name',
                'value' => 'Centrum stredného školstva Vranov nad Topľou',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Názov školy',
                'description' => 'Oficiálny názov školy pre dokumenty a exporty',
                'is_public' => true,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'string', 'max:255']),
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_address',
                'value' => 'Nemocničná 1, 093 01 Vranov nad Topľou',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Adresa školy',
                'description' => 'Adresa školy pre dokumenty',
                'is_public' => true,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'string']),
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_number_prefix',
                'value' => 'INV',
                'type' => 'string',
                'group' => 'inventory',
                'label' => 'Prefix inventárneho čísla',
                'description' => 'Prefix pre automaticky generované inventárne čísla',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'string', 'max:10']),
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_number_length',
                'value' => '6',
                'type' => 'integer',
                'group' => 'inventory',
                'label' => 'Dĺžka inventárneho čísla',
                'description' => 'Počet číslic v inventárnom čísle (bez prefixu)',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:4', 'max:10']),
                'sort_order' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'auto_generate_inventory_numbers',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'inventory',
                'label' => 'Automatické generovanie inventárnych čísel',
                'description' => 'Automaticky generovať inventárne čísla pri vytváraní nového majetku',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'sort_order' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_plan_duration_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'inventory',
                'label' => 'Predvolená dĺžka inventarizácie (dni)',
                'description' => 'Predvolený počet dní na dokončenie inventarizácie',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:1', 'max:365']),
                'sort_order' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'export_format_default',
                'value' => 'xlsx',
                'type' => 'select',
                'group' => 'export',
                'label' => 'Predvolený formát exportu',
                'description' => 'Predvolený formát pre export správ',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'in:xlsx,pdf']),
                'options' => json_encode([
                    'xlsx' => 'Excel (XLSX)',
                    'pdf' => 'PDF'
                ]),
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'Emailové upozornenia zapnuté',
                'description' => 'Povoliť zasielanie emailových upozornení',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'session_timeout_minutes',
                'value' => '120',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Timeout relácie (minúty)',
                'description' => 'Automatické odhlásenie po nečinnosti (v minútach)',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:15', 'max:480']),
                'sort_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_file_upload_size_mb',
                'value' => '10',
                'type' => 'integer',
                'group' => 'security',
                'label' => 'Maximálna veľkosť súboru (MB)',
                'description' => 'Maximálna veľkosť uploadovaného súboru v megabajtoch',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:1', 'max:100']),
                'sort_order' => 41,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->insert($setting);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
};