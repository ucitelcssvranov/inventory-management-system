<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->instance('path.config', __DIR__ . '/config');
$app->bind('config', function () use ($app) {
    return new \Illuminate\Config\Repository(require __DIR__ . '/config/app.php');
});

use Illuminate\Support\Facades\DB;

try {
    // Najprv skontrolujeme, či už nejaké nastavenia existujú
    $count = DB::table('system_settings')->count();
    
    if ($count > 0) {
        echo "Nastavenia už existujú ({$count} záznamov). Preskakujem vloženie.\n";
        exit(0);
    }

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
        ]
    ];

    foreach ($settings as $setting) {
        DB::table('system_settings')->insert($setting);
        echo "Vložené nastavenie: {$setting['key']}\n";
    }

    echo "Všetky nastavenia boli úspešne vložené!\n";

} catch (Exception $e) {
    echo "Chyba pri vkladaní nastavení: " . $e->getMessage() . "\n";
    exit(1);
}