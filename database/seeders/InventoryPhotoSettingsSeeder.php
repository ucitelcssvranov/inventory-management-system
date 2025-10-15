<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryPhotoSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $photoSettings = [
            [
                'key' => 'inventory_photo_required',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'inventory',
                'label' => 'Povinnosť fotografie pri inventúre',
                'description' => 'Vyžadovať fotografiu pri zaznamenávaní inventúrneho počtu (voliteľné)',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'options' => null,
                'sort_order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_photo_max_size',
                'value' => '5120',
                'type' => 'select',
                'group' => 'inventory',
                'label' => 'Maximálna veľkosť fotografie',
                'description' => 'Maximálna veľkosť fotografie v KB',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'in:1024,2048,5120,10240']),
                'options' => json_encode([
                    '1024' => '1 MB',
                    '2048' => '2 MB',
                    '5120' => '5 MB',
                    '10240' => '10 MB'
                ]),
                'sort_order' => 51,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_photo_min_width',
                'value' => '800',
                'type' => 'integer',
                'group' => 'inventory',
                'label' => 'Minimálna šírka fotografie',
                'description' => 'Minimálna šírka fotografie v pixeloch',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:300', 'max:4000']),
                'options' => null,
                'sort_order' => 52,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_photo_min_height',
                'value' => '600',
                'type' => 'integer',
                'group' => 'inventory',
                'label' => 'Minimálna výška fotografie',
                'description' => 'Minimálna výška fotografie v pixeloch',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:300', 'max:4000']),
                'options' => null,
                'sort_order' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_photo_allowed_formats',
                'value' => 'jpg,jpeg,png,webp',
                'type' => 'text',
                'group' => 'inventory',
                'label' => 'Povolené formáty fotografií',
                'description' => 'Zoznam povolených formátov oddelených čiarkou',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'string']),
                'options' => null,
                'sort_order' => 54,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_photo_storage_path',
                'value' => 'inventory-photos',
                'type' => 'string',
                'group' => 'inventory',
                'label' => 'Priečinok pre fotografie',
                'description' => 'Relatívna cesta v storage/app/public kde sa ukladajú fotografie',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'string', 'max:255']),
                'options' => null,
                'sort_order' => 55,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_photo_quality',
                'value' => '85',
                'type' => 'select',
                'group' => 'inventory',
                'label' => 'Kvalita kompresie JPEG',
                'description' => 'Kvalita kompresie pre JPEG fotografie (0-100)',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'in:60,70,80,85,90,95']),
                'options' => json_encode([
                    '60' => 'Nízka (60%)',
                    '70' => 'Stredná (70%)',
                    '80' => 'Dobrá (80%)',
                    '85' => 'Vysoká (85%)',
                    '90' => 'Veľmi vysoká (90%)',
                    '95' => 'Maximálna (95%)'
                ]),
                'sort_order' => 56,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_photo_watermark',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'inventory',
                'label' => 'Pridať vodotlač na fotografie',
                'description' => 'Automaticky pridať vodotlač s dátumom a časom inventúry',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'options' => null,
                'sort_order' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_photo_auto_resize',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'inventory',
                'label' => 'Automatická zmena veľkosti',
                'description' => 'Automaticky zmeniť veľkosť veľkých fotografií na maximálne 1920x1080',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'options' => null,
                'sort_order' => 58,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'inventory_multiple_photos',
                'value' => '3',
                'type' => 'select',
                'group' => 'inventory',
                'label' => 'Maximálny počet fotografií na asset',
                'description' => 'Koľko fotografií môže byť nahraných na jeden asset počas inventúry',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'in:1,2,3,5,10']),
                'options' => json_encode([
                    '1' => '1 fotografia',
                    '2' => '2 fotografie',
                    '3' => '3 fotografie',
                    '5' => '5 fotografií',
                    '10' => '10 fotografií'
                ]),
                'sort_order' => 59,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert new photo settings
        foreach ($photoSettings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}