<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class CommissionAutoAssignmentSettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'key' => 'commission_auto_assignment_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'commission',
                'label' => 'Automatické priraďovanie komisií',
                'description' => 'Povoliť automatické priraďovanie komisií k inventarizačným plánom',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'options' => null,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'commission_assignment_strategy',
                'value' => 'balanced_workload',
                'type' => 'select',
                'group' => 'commission',
                'label' => 'Stratégia priraďovania',
                'description' => 'Algoritmus pre výber komisie',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'in:balanced_workload,location_based,category_specialization,least_busy,round_robin']),
                'options' => json_encode([
                    'balanced_workload' => 'Vyrovnané zaťaženie',
                    'location_based' => 'Podľa lokácie',
                    'category_specialization' => 'Špecializácia kategórií',
                    'least_busy' => 'Najmenej zaneprázdnená',
                    'round_robin' => 'Striedavo postupne'
                ]),
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'commission_workload_threshold',
                'value' => '10',
                'type' => 'integer',
                'group' => 'commission',
                'label' => 'Max. počet plánov na komisiu',
                'description' => 'Maximálny počet aktívnych plánov priradených jednej komisii',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:1', 'max:50']),
                'options' => null,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'commission_location_priority',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'commission',
                'label' => 'Priorita lokácie',
                'description' => 'Uprednostniť komisie s členom z tej istej lokácie/budovy',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'options' => null,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'commission_category_specialization',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'commission',
                'label' => 'Špecializácia kategórií',
                'description' => 'Priradiť komisie na základe ich špecializácie na kategórie',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'options' => null,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'commission_auto_notification',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'commission',
                'label' => 'Automatické notifikácie',
                'description' => 'Odoslať notifikáciu komisii pri automatickom priradení',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'options' => null,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'commission_fallback_strategy',
                'value' => 'least_busy',
                'type' => 'select',
                'group' => 'commission',
                'label' => 'Záložná stratégia',
                'description' => 'Čo robiť ak primárna stratégia nenájde vhodnú komisiu',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'in:least_busy,round_robin,manual_assignment']),
                'options' => json_encode([
                    'least_busy' => 'Najmenej zaneprázdnená',
                    'round_robin' => 'Striedavo postupne',
                    'manual_assignment' => 'Manuálne priradenie'
                ]),
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'commission_min_members',
                'value' => '2',
                'type' => 'integer',
                'group' => 'commission',
                'label' => 'Min. počet členov komisie',
                'description' => 'Minimálny počet členov potrebný na automatické priradenie',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:1', 'max:10']),
                'options' => null,
                'sort_order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'commission_assignment_log',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'commission',
                'label' => 'Logovanie priraďovania',
                'description' => 'Zaznamenávať históriu automatického priraďovania komisií',
                'is_public' => false,
                'is_editable' => true,
                'validation_rules' => json_encode(['boolean']),
                'options' => null,
                'sort_order' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Vytvorených 9 nastavení pre automatické priraďovanie komisií');
    }
}