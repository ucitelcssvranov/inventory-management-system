<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use App\Models\InventoryCommission;
use App\Models\Asset;

class CacheService
{
    // Cache keys
    const CACHE_CATEGORIES = 'inventory.categories';
    const CACHE_LOCATIONS = 'inventory.locations';
    const CACHE_LOCATIONS_BY_CATEGORY = 'inventory.locations.category.';
    const CACHE_USERS = 'inventory.users';
    const CACHE_COMMISSIONS = 'inventory.commissions';
    const CACHE_COMMISSION_MEMBERS = 'inventory.commission.members.';
    const CACHE_DASHBOARD_STATS = 'inventory.dashboard.stats';
    const CACHE_RECENT_ASSETS = 'inventory.recent.assets';

    // Cache durations (v minútach)
    const TTL_SHORT = 5;      // 5 minút pre často menené dáta
    const TTL_MEDIUM = 30;    // 30 minút pre stredne často menené dáta
    const TTL_LONG = 120;     // 2 hodiny pre zriedka menené dáta
    const TTL_DAILY = 1440;   // 24 hodín pre veľmi stabilné dáta

    /**
     * Získa všetky kategórie z cache alebo databázy
     */
    public function getCategories()
    {
        return Cache::remember(self::CACHE_CATEGORIES, self::TTL_LONG, function () {
            Log::info('Loading categories from database - cache miss');
            return Category::orderBy('name')->get(['id', 'name', 'description']);
        });
    }

    /**
     * Získa všetky lokácie z cache alebo databázy
     */
    public function getLocations()
    {
        return Cache::remember(self::CACHE_LOCATIONS, self::TTL_MEDIUM, function () {
            Log::info('Loading locations from database - cache miss');
            return Location::orderBy('name')
                ->get(['id', 'name', 'description', 'type', 'parent_id', 'room_number']);
        });
    }

    /**
     * Získa lokácie pre danú kategóriu/parent ID
     * V novej štruktúre: ak je categoryId = 0, vráti budovy, inak vráti miestnosti pre danú budovu
     */
    public function getLocationsByCategory(int $categoryId)
    {
        $cacheKey = self::CACHE_LOCATIONS_BY_CATEGORY . $categoryId;
        
        return Cache::remember($cacheKey, self::TTL_MEDIUM, function () use ($categoryId) {
            Log::info("Loading locations for category/parent {$categoryId} from database - cache miss");
            
            if ($categoryId == 0) {
                // Vráti všetky budovy (locations bez parent_id)
                return Location::whereNull('parent_id')
                    ->where('type', 'budova')
                    ->orderBy('name')
                    ->get(['id', 'name', 'description']);
            } else {
                // Vráti miestnosti pre danú budovu (parent_id = categoryId)
                return Location::where('parent_id', $categoryId)
                    ->where('type', 'miestnost')
                    ->orderBy('name')
                    ->get(['id', 'name', 'description', 'room_number']);
            }
        });
    }

    /**
     * Získa všetkých používateľov z cache alebo databázy
     */
    public function getUsers()
    {
        return Cache::remember(self::CACHE_USERS, self::TTL_MEDIUM, function () {
            Log::info('Loading users from database - cache miss');
            return User::orderBy('name')->get(['id', 'name', 'email']);
        });
    }

    /**
     * Získa všetky komisie z cache alebo databázy
     */
    public function getCommissions()
    {
        return Cache::remember(self::CACHE_COMMISSIONS, self::TTL_LONG, function () {
            Log::info('Loading commissions from database - cache miss');
            return InventoryCommission::orderBy('name')
                ->get(['id', 'name', 'description']);
        });
    }

    /**
     * Získa členov komisie
     */
    public function getCommissionMembers(int $commissionId)
    {
        $cacheKey = self::CACHE_COMMISSION_MEMBERS . $commissionId;
        
        return Cache::remember($cacheKey, self::TTL_MEDIUM, function () use ($commissionId) {
            Log::info("Loading commission {$commissionId} members from database - cache miss");
            
            return User::whereIn('id', function($query) use ($commissionId) {
                $query->select('user_id')
                    ->from('inventory_commission_members')
                    ->where('inventory_commission_id', $commissionId);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        });
    }

    /**
     * Získa štatistiky pre dashboard
     */
    public function getDashboardStats()
    {
        return Cache::remember(self::CACHE_DASHBOARD_STATS, self::TTL_SHORT, function () {
            Log::info('Loading dashboard stats from database - cache miss');
            
            return [
                'total_assets' => Asset::count(),
                'total_locations' => Location::count(),
                'total_users' => User::count(),
                'total_commissions' => InventoryCommission::count(),
                'active_plans' => \App\Models\InventoryPlan::where('status', 'active')->count(),
                'recent_assets' => Asset::whereDate('created_at', '>=', now()->subDays(30))->count(),
                'total_value' => Asset::sum('acquisition_cost'),
                'categories_count' => Category::count(),
            ];
        });
    }

    /**
     * Získa nedávno pridané assets
     */
    public function getRecentAssets(int $limit = 10)
    {
        $cacheKey = self::CACHE_RECENT_ASSETS . '.' . $limit;
        
        return Cache::remember($cacheKey, self::TTL_SHORT, function () use ($limit) {
            Log::info("Loading {$limit} recent assets from database - cache miss");
            
            return Asset::with(['category:id,name', 'location:id,name'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get(['id', 'name', 'inventory_number', 'category_id', 'location_id', 'acquisition_cost', 'created_at']);
        });
    }

    /**
     * Vymaže cache pre kategórie
     */
    public function forgetCategories()
    {
        Cache::forget(self::CACHE_CATEGORIES);
        Log::info('Categories cache cleared');
    }

    /**
     * Vymaže cache pre lokácie
     */
    public function forgetLocations()
    {
        Cache::forget(self::CACHE_LOCATIONS);
        
        // Vymaž aj cache pre lokácie podľa kategórií
        $this->forgetLocationsByCategory();
        
        Log::info('Locations cache cleared');
    }

    /**
     * Vymaže cache pre lokácie všetkých kategórií
     */
    public function forgetLocationsByCategory(int $categoryId = null)
    {
        if ($categoryId) {
            Cache::forget(self::CACHE_LOCATIONS_BY_CATEGORY . $categoryId);
            Log::info("Locations cache for category {$categoryId} cleared");
        } else {
            // Vymaž pre všetky kategórie (trochu brute force, ale bezpečné)
            $categories = Category::pluck('id');
            foreach ($categories as $catId) {
                Cache::forget(self::CACHE_LOCATIONS_BY_CATEGORY . $catId);
            }
            Log::info('All locations by category cache cleared');
        }
    }

    /**
     * Vymaže cache pre používateľov
     */
    public function forgetUsers()
    {
        Cache::forget(self::CACHE_USERS);
        Log::info('Users cache cleared');
    }

    /**
     * Vymaže cache pre komisie
     */
    public function forgetCommissions()
    {
        Cache::forget(self::CACHE_COMMISSIONS);
        
        // Vymaž aj cache pre členov komisií
        $this->forgetAllCommissionMembers();
        
        Log::info('Commissions cache cleared');
    }

    /**
     * Vymaže cache pre členov komisie
     */
    public function forgetCommissionMembers(int $commissionId)
    {
        Cache::forget(self::CACHE_COMMISSION_MEMBERS . $commissionId);
        Log::info("Commission {$commissionId} members cache cleared");
    }

    /**
     * Vymaže cache pre členov všetkých komisií
     */
    public function forgetAllCommissionMembers()
    {
        $commissions = InventoryCommission::pluck('id');
        foreach ($commissions as $commissionId) {
            Cache::forget(self::CACHE_COMMISSION_MEMBERS . $commissionId);
        }
        Log::info('All commission members cache cleared');
    }

    /**
     * Vymaže cache pre dashboard štatistiky
     */
    public function forgetDashboardStats()
    {
        Cache::forget(self::CACHE_DASHBOARD_STATS);
        Log::info('Dashboard stats cache cleared');
    }

    /**
     * Vymaže cache pre nedávne assets
     */
    public function forgetRecentAssets()
    {
        // Vymaž všetky varianty recent assets cache
        $limits = [5, 10, 15, 20]; // možné limity
        foreach ($limits as $limit) {
            Cache::forget(self::CACHE_RECENT_ASSETS . '.' . $limit);
        }
        Log::info('Recent assets cache cleared');
    }

    /**
     * Vymaže všetky cache súvisiace s inventárom
     */
    public function forgetAll()
    {
        $this->forgetCategories();
        $this->forgetLocations();
        $this->forgetUsers();
        $this->forgetCommissions();
        $this->forgetDashboardStats();
        $this->forgetRecentAssets();
        
        Log::info('All inventory cache cleared');
    }

    /**
     * Predhreje cache s často používanými dátami
     */
    public function warmUp()
    {
        Log::info('Starting cache warm-up');
        
        // Predhrej základné dáta
        $this->getCategories();
        $this->getLocations();
        $this->getUsers();
        $this->getCommissions();
        $this->getDashboardStats();
        $this->getRecentAssets();
        
        // Predhrej lokácie pre každú kategóriu
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            $this->getLocationsByCategory($category->id);
        }
        
        // Predhrej členov pre každú komisiu
        $commissions = $this->getCommissions();
        foreach ($commissions as $commission) {
            $this->getCommissionMembers($commission->id);
        }
        
        Log::info('Cache warm-up completed');
    }

    /**
     * Získa informácie o stave cache
     */
    public function getCacheInfo()
    {
        $keys = [
            'categories' => self::CACHE_CATEGORIES,
            'locations' => self::CACHE_LOCATIONS,
            'users' => self::CACHE_USERS,
            'commissions' => self::CACHE_COMMISSIONS,
            'dashboard_stats' => self::CACHE_DASHBOARD_STATS,
            'recent_assets' => self::CACHE_RECENT_ASSETS . '.10',
        ];

        $info = [];
        foreach ($keys as $name => $key) {
            $info[$name] = [
                'key' => $key,
                'exists' => Cache::has($key),
                'data_count' => Cache::has($key) ? count(Cache::get($key, [])) : 0,
            ];
        }

        return $info;
    }
}