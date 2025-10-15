<?php

namespace App\Services;

use App\Models\InventoryPlan;
use App\Models\InventoryCommission;
use App\Models\CommissionSpecialization;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class CommissionAutoAssignmentService
{
    protected $settings;

    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Načíta nastavenia z databázy
     */
    protected function loadSettings()
    {
        $this->settings = SystemSetting::where('group', 'commission')
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Automaticky priradi komisiu k inventarizačnému plánu
     */
    public function assignCommission(InventoryPlan $plan): ?InventoryCommission
    {
        if (!$this->isAutoAssignmentEnabled()) {
            return null;
        }

        $strategy = $this->settings['commission_assignment_strategy'] ?? 'balanced_workload';
        
        $commission = match($strategy) {
            'balanced_workload' => $this->findCommissionByBalancedWorkload($plan),
            'location_based' => $this->findCommissionByLocation($plan),
            'category_specialization' => $this->findCommissionBySpecialization($plan),
            'least_busy' => $this->findLeastBusyCommission($plan),
            'round_robin' => $this->findCommissionByRoundRobin($plan),
            default => $this->findCommissionByBalancedWorkload($plan)
        };

        // Ak primárna stratégia zlyhala, použije záložnú
        if (!$commission) {
            $commission = $this->applyFallbackStrategy($plan);
        }

        if ($commission) {
            $this->performAssignment($plan, $commission, $strategy);
            $this->logAssignment($plan, $commission, $strategy);
            
            if ($this->isNotificationEnabled()) {
                $this->sendNotification($plan, $commission);
            }
        }

        return $commission;
    }

    /**
     * Stratégia: Vyrovnané zaťaženie
     */
    protected function findCommissionByBalancedWorkload(InventoryPlan $plan): ?InventoryCommission
    {
        $maxWorkload = (int)($this->settings['commission_workload_threshold'] ?? 10);
        $minMembers = (int)($this->settings['commission_min_members'] ?? 2);

        // Získaj komisie s počtom aktívnych plánov
        $commissions = InventoryCommission::with(['chairman', 'members'])
            ->withCount([
                'inventoryPlans as active_plans_count' => function($query) {
                    $query->whereIn('status', [
                        InventoryPlan::STATUS_APPROVED,
                        InventoryPlan::STATUS_ASSIGNED,
                        InventoryPlan::STATUS_IN_PROGRESS
                    ]);
                }
            ])
            ->where(function($query) {
                // Komisie ktoré majú predsedu (aktívne komisie)
                $query->whereNotNull('chairman_id');
            })
            ->having('active_plans_count', '<', $maxWorkload)
            ->orderBy('active_plans_count')
            ->get();

        // Filtruj komisie s dostatočným počtom členov
        $commissions = $commissions->filter(function($commission) use ($minMembers) {
            return $commission->allMembers()->count() >= $minMembers;
        });

        // Ak je povolená lokačná priorita, uprednostni komisie z tej istej lokácie
        if ($this->isLocationPriorityEnabled() && $plan->location_id) {
            $locationCommissions = $this->filterCommissionsByLocation($commissions, $plan->location_id);
            if ($locationCommissions->isNotEmpty()) {
                return $locationCommissions->first();
            }
        }

        // Ak je povolená špecializácia, uprednostni komisie špecializované na kategóriu
        if ($this->isCategorySpecializationEnabled() && $plan->category_id) {
            $specializedCommissions = $this->filterCommissionsBySpecialization($commissions, $plan->category_id);
            if ($specializedCommissions->isNotEmpty()) {
                return $specializedCommissions->first();
            }
        }

        return $commissions->first();
    }

    /**
     * Stratégia: Podľa lokácie
     */
    protected function findCommissionByLocation(InventoryPlan $plan): ?InventoryCommission
    {
        if (!$plan->location_id) {
            return $this->findLeastBusyCommission($plan);
        }

        $commissions = $this->getEligibleCommissions();
        return $this->filterCommissionsByLocation($commissions, $plan->location_id)->first();
    }

    /**
     * Stratégia: Špecializácia kategórií
     */
    protected function findCommissionBySpecialization(InventoryPlan $plan): ?InventoryCommission
    {
        if (!$plan->category_id) {
            return $this->findLeastBusyCommission($plan);
        }

        $commissions = $this->getEligibleCommissions();
        return $this->filterCommissionsBySpecialization($commissions, $plan->category_id)->first();
    }

    /**
     * Stratégia: Najmenej zaneprázdnená
     */
    protected function findLeastBusyCommission(InventoryPlan $plan): ?InventoryCommission
    {
        return $this->getEligibleCommissions()
            ->sortBy('active_plans_count')
            ->first();
    }

    /**
     * Stratégia: Round Robin
     */
    protected function findCommissionByRoundRobin(InventoryPlan $plan): ?InventoryCommission
    {
        $lastAssignedCommissionId = cache('last_assigned_commission_id', 0);
        
        $commissions = $this->getEligibleCommissions();
        
        // Nájdi ďalšiu komisiu v poradí
        $nextCommission = $commissions->where('id', '>', $lastAssignedCommissionId)->first();
        
        if (!$nextCommission) {
            // Ak sme na konci, začni znovu
            $nextCommission = $commissions->first();
        }

        if ($nextCommission) {
            cache(['last_assigned_commission_id' => $nextCommission->id], now()->addDay());
        }

        return $nextCommission;
    }

    /**
     * Aplikuje záložnú stratégiu
     */
    protected function applyFallbackStrategy(InventoryPlan $plan): ?InventoryCommission
    {
        $fallbackStrategy = $this->settings['commission_fallback_strategy'] ?? 'least_busy';
        
        return match($fallbackStrategy) {
            'least_busy' => $this->findLeastBusyCommission($plan),
            'round_robin' => $this->findCommissionByRoundRobin($plan),
            'manual_assignment' => null, // Ponechá na manuálne priradenie
            default => $this->findLeastBusyCommission($plan)
        };
    }

    /**
     * Získa komisie spĺňajúce základné kritériá
     */
    protected function getEligibleCommissions(): Collection
    {
        $maxWorkload = (int)($this->settings['commission_workload_threshold'] ?? 10);
        $minMembers = (int)($this->settings['commission_min_members'] ?? 2);

        $commissions = InventoryCommission::with(['chairman', 'members'])
            ->withCount([
                'inventoryPlans as active_plans_count' => function($query) {
                    $query->whereIn('status', [
                        InventoryPlan::STATUS_APPROVED,
                        InventoryPlan::STATUS_ASSIGNED,
                        InventoryPlan::STATUS_IN_PROGRESS
                    ]);
                }
            ])
            ->having('active_plans_count', '<', $maxWorkload)
            ->get();

        // Filtruj komisie s dostatočným počtom členov
        return $commissions->filter(function($commission) use ($minMembers) {
            return $commission->allMembers()->count() >= $minMembers;
        });
    }

    /**
     * Filtruje komisie podľa lokácie
     */
    protected function filterCommissionsByLocation(Collection $commissions, int $locationId): Collection
    {
        // TODO: Implementovať logiku na základe lokácie členov komisie
        // Napríklad, ak má komisia člena z tej istej budovy/oddelenia
        return $commissions;
    }

    /**
     * Filtruje komisie podľa špecializácie na kategóriu
     */
    protected function filterCommissionsBySpecialization(Collection $commissions, int $categoryId): Collection
    {
        // Najprv skús nájsť komisie s explicitnou špecializáciou
        $specializedCommissions = $commissions->filter(function($commission) use ($categoryId) {
            return $commission->specializations()
                ->where('category_id', $categoryId)
                ->exists();
        });

        if ($specializedCommissions->isNotEmpty()) {
            // Zoradi podľa úrovne skúseností (najvyššia najprv)
            return $specializedCommissions->sortByDesc(function($commission) use ($categoryId) {
                $specialization = $commission->specializations()
                    ->where('category_id', $categoryId)
                    ->first();
                return $specialization ? $specialization->experience_level : 0;
            });
        }

        // Ak neexistujú špecializácie, použije základné mapovanie na základe názvu komisie
        return $commissions->filter(function($commission) use ($categoryId) {
            $commissionName = strtolower($commission->name);
            
            // Základné mapovanie kategórií na komisie
            $categoryMappings = [
                'výpočtová technika' => ['informatika', 'ict', 'počítač'],
                'kancelárska technika' => ['administratíva', 'kancelár'],
                'laboratórne vybavenie' => ['laboratórium', 'prírodovedné'],
                'športové vybavenie' => ['telocvik', 'šport'],
                'nábytok' => ['nábytok', 'vybavenie'],
            ];

            // TODO: Získať skutočný názov kategórie z databázy
            // $category = Category::find($categoryId);
            
            foreach ($categoryMappings as $categoryKeywords) {
                foreach ($categoryKeywords as $keyword) {
                    if (str_contains($commissionName, $keyword)) {
                        return true;
                    }
                }
            }
            
            return false;
        });
    }

    /**
     * Vykoná priradenie komisie k plánu
     */
    protected function performAssignment(InventoryPlan $plan, InventoryCommission $commission, string $strategy): void
    {
        $plan->update([
            'commission_id' => $commission->id,
            'status' => InventoryPlan::STATUS_ASSIGNED,
            'updated_by' => auth()->id() ?? 1
        ]);
    }

    /**
     * Zaloguje priradenie
     */
    protected function logAssignment(InventoryPlan $plan, InventoryCommission $commission, string $strategy): void
    {
        if ($this->isLoggingEnabled()) {
            Log::info('Automatické priradenie komisie', [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'commission_id' => $commission->id,
                'commission_name' => $commission->name,
                'strategy' => $strategy,
                'timestamp' => now()
            ]);
        }
    }

    /**
     * Pošle notifikáciu komisii
     */
    protected function sendNotification(InventoryPlan $plan, InventoryCommission $commission): void
    {
        // TODO: Implementovať notifikačný systém
        // Napríklad email alebo in-app notifikácie
    }

    // Helper metódy pre nastavenia

    protected function isAutoAssignmentEnabled(): bool
    {
        return (bool)($this->settings['commission_auto_assignment_enabled'] ?? false);
    }

    protected function isLocationPriorityEnabled(): bool
    {
        return (bool)($this->settings['commission_location_priority'] ?? false);
    }

    protected function isCategorySpecializationEnabled(): bool
    {
        return (bool)($this->settings['commission_category_specialization'] ?? false);
    }

    protected function isNotificationEnabled(): bool
    {
        return (bool)($this->settings['commission_auto_notification'] ?? false);
    }

    protected function isLoggingEnabled(): bool
    {
        return (bool)($this->settings['commission_assignment_log'] ?? false);
    }

    /**
     * Získa štatistiky automatického priraďovania
     */
    public function getAssignmentStats(): array
    {
        // TODO: Implementovať štatistiky
        return [
            'total_auto_assignments' => 0,
            'success_rate' => 0,
            'most_used_strategy' => 'balanced_workload',
            'commission_utilization' => []
        ];
    }
}