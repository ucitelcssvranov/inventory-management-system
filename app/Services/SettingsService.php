<?php

namespace App\Services;

class SettingsService
{
    /**
     * Get icon for settings group
     */
    public static function getGroupIcon(string $group): string
    {
        return match($group) {
            'general' => 'house',
            'inventory' => 'archive',
            'export' => 'download',
            'notifications' => 'bell',
            'qr_codes' => 'qr-code',
            'security' => 'shield-check',
            'system' => 'cpu',
            default => 'gear'
        };
    }

    /**
     * Get title for settings group
     */
    public static function getGroupTitle(string $group): string
    {
        return match($group) {
            'general' => 'Všeobecné nastavenia',
            'inventory' => 'Inventarizácia',
            'export' => 'Export a správy',
            'notifications' => 'Upozornenia',
            'qr_codes' => 'QR kódy',
            'security' => 'Bezpečnosť',
            'system' => 'Systém',
            default => ucfirst($group)
        };
    }

    /**
     * Get validation rules for a setting type
     */
    public static function getValidationRules(string $type, array $customRules = []): array
    {
        $baseRules = match($type) {
            'boolean' => ['boolean'],
            'integer' => ['integer'],
            'string' => ['string', 'max:255'],
            'text' => ['string'],
            'json' => ['json'],
            'select' => ['string'],
            default => ['string']
        };

        return array_merge($baseRules, $customRules);
    }

    /**
     * Get default settings structure
     */
    public static function getDefaultSettings(): array
    {
        return [
            'general' => [
                'system_name' => [
                    'value' => 'Inventarizačný systém CSŠ Vranov',
                    'type' => 'string',
                    'label' => 'Názov systému',
                    'description' => 'Názov systému zobrazovaný v hlavičke a dokumentoch',
                    'is_public' => true,
                    'validation_rules' => ['required', 'string', 'max:255']
                ],
                'school_name' => [
                    'value' => 'Centrum stredného školstva Vranov nad Topľou',
                    'type' => 'string',
                    'label' => 'Názov školy',
                    'description' => 'Oficiálny názov školy pre dokumenty a exporty',
                    'is_public' => true,
                    'validation_rules' => ['required', 'string', 'max:255']
                ]
            ],
            'inventory' => [
                'inventory_number_format' => [
                    'value' => 'YYYY-ID',
                    'type' => 'select',
                    'label' => 'Formát inventárneho čísla',
                    'description' => 'Formát pre generovanie inventárnych čísel',
                    'is_public' => false,
                    'validation_rules' => ['required', 'in:YYYY-ID,sequential'],
                    'options' => [
                        'YYYY-ID' => 'Rok-ID (2024-123)',
                        'sequential' => 'Sekvenčné číslovanie (000001)'
                    ]
                ],
                'auto_generate_inventory_numbers' => [
                    'value' => '1',
                    'type' => 'boolean',
                    'label' => 'Automatické generovanie inventárnych čísel',
                    'description' => 'Automaticky generovať inventárne čísla pri vytváraní nového majetku',
                    'is_public' => false,
                    'validation_rules' => ['boolean']
                ]
            ]
        ];
    }
}