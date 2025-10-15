<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
                'key' => 'inventory_number_format',
                'value' => 'YYYY-ID',
                'type' => 'select',
                'group' => 'inventory',
                'label' => 'Formát inventárneho čísla',
                'description' => 'Formát pre generovanie inventárnych čísel',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'in:YYYY-ID,sequential']),
                'options' => json_encode([
                    'YYYY-ID' => 'Rok-ID (2024-123)',
                    'sequential' => 'Sekvenčné číslovanie (000001)'
                ]),
                'sort_order' => 10,
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
                'sort_order' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_validation_strict',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'inventory',
                'label' => 'Striktná validácia inventárnych čísel',
                'description' => 'Kontrolovať konzistenciu inventárnych čísel s dátumom nadobudnutia',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'sort_order' => 12,
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
            ]
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
