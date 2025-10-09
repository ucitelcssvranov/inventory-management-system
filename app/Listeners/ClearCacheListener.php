<?php

namespace App\Listeners;

use App\Services\CacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ClearCacheListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected $cacheService;

    /**
     * Create the event listener.
     */
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the event when category is created/updated/deleted.
     */
    public function handleCategoryChange($event)
    {
        $this->cacheService->forgetCategories();
        $this->cacheService->forgetLocations(); // locations majú category_id
        $this->cacheService->forgetDashboardStats();
    }

    /**
     * Handle the event when location is created/updated/deleted.
     */
    public function handleLocationChange($event)
    {
        $this->cacheService->forgetLocations();
        
        // Ak má location parent_id, vymaž cache pre parent aj pre seba
        if (isset($event->location)) {
            if ($event->location->parent_id) {
                $this->cacheService->forgetLocationsByCategory($event->location->parent_id);
            }
            // Vymaž aj cache pre všetky child locations ak je to budova
            if ($event->location->type === 'budova') {
                $this->cacheService->forgetLocationsByCategory($event->location->id);
            }
            $this->cacheService->forgetLocationsByCategory(); // vymaž všetky
        } else {
            $this->cacheService->forgetLocationsByCategory(); // vymaž všetky
        }
        
        $this->cacheService->forgetDashboardStats();
    }

    /**
     * Handle the event when user is created/updated/deleted.
     */
    public function handleUserChange($event)
    {
        $this->cacheService->forgetUsers();
        $this->cacheService->forgetAllCommissionMembers();
        $this->cacheService->forgetDashboardStats();
    }

    /**
     * Handle the event when commission is created/updated/deleted.
     */
    public function handleCommissionChange($event)
    {
        $this->cacheService->forgetCommissions();
        
        // Ak máme ID komisie, vymaž špecificky
        if (isset($event->commission) && $event->commission->id) {
            $this->cacheService->forgetCommissionMembers($event->commission->id);
        } else {
            $this->cacheService->forgetAllCommissionMembers();
        }
        
        $this->cacheService->forgetDashboardStats();
    }

    /**
     * Handle the event when asset is created/updated/deleted.
     */
    public function handleAssetChange($event)
    {
        $this->cacheService->forgetDashboardStats();
        $this->cacheService->forgetRecentAssets();
    }

    /**
     * Handle the event when inventory plan is created/updated/deleted.
     */
    public function handleInventoryPlanChange($event)
    {
        $this->cacheService->forgetDashboardStats();
    }
}