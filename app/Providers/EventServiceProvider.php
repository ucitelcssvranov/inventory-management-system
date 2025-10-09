<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            \SocialiteProviders\Azure\AzureExtendSocialite::class.'@handle',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Cache clearing listeners for Eloquent model events
        $this->registerModelCacheListeners();
    }

    /**
     * Register cache clearing listeners for model events
     */
    protected function registerModelCacheListeners()
    {
        $cacheListener = app(\App\Listeners\ClearCacheListener::class);

        // Category events
        Event::listen('eloquent.created: App\Models\Category', [$cacheListener, 'handleCategoryChange']);
        Event::listen('eloquent.updated: App\Models\Category', [$cacheListener, 'handleCategoryChange']);
        Event::listen('eloquent.deleted: App\Models\Category', [$cacheListener, 'handleCategoryChange']);

        // Location events
        Event::listen('eloquent.created: App\Models\Location', [$cacheListener, 'handleLocationChange']);
        Event::listen('eloquent.updated: App\Models\Location', [$cacheListener, 'handleLocationChange']);
        Event::listen('eloquent.deleted: App\Models\Location', [$cacheListener, 'handleLocationChange']);

        // User events
        Event::listen('eloquent.created: App\Models\User', [$cacheListener, 'handleUserChange']);
        Event::listen('eloquent.updated: App\Models\User', [$cacheListener, 'handleUserChange']);
        Event::listen('eloquent.deleted: App\Models\User', [$cacheListener, 'handleUserChange']);

        // Commission events
        Event::listen('eloquent.created: App\Models\InventoryCommission', [$cacheListener, 'handleCommissionChange']);
        Event::listen('eloquent.updated: App\Models\InventoryCommission', [$cacheListener, 'handleCommissionChange']);
        Event::listen('eloquent.deleted: App\Models\InventoryCommission', [$cacheListener, 'handleCommissionChange']);

        // Asset events
        Event::listen('eloquent.created: App\Models\Asset', [$cacheListener, 'handleAssetChange']);
        Event::listen('eloquent.updated: App\Models\Asset', [$cacheListener, 'handleAssetChange']);
        Event::listen('eloquent.deleted: App\Models\Asset', [$cacheListener, 'handleAssetChange']);

        // Inventory Plan events
        Event::listen('eloquent.created: App\Models\InventoryPlan', [$cacheListener, 'handleInventoryPlanChange']);
        Event::listen('eloquent.updated: App\Models\InventoryPlan', [$cacheListener, 'handleInventoryPlanChange']);
        Event::listen('eloquent.deleted: App\Models\InventoryPlan', [$cacheListener, 'handleInventoryPlanChange']);
    }
}
