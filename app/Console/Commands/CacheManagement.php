<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;

class CacheManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:cache 
                            {action : clear, warmup, info, or status}
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spravuje cache pre inventárny systém (clear, warmup, info, status)';

    protected $cacheService;

    /**
     * Create a new command instance.
     */
    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'clear':
                return $this->clearCache();
            case 'warmup':
                return $this->warmUpCache();
            case 'info':
            case 'status':
                return $this->showCacheInfo();
            default:
                $this->error("Neznáma akcia: {$action}");
                $this->info('Dostupné akcie: clear, warmup, info, status');
                return 1;
        }
    }

    /**
     * Clear all inventory cache
     */
    protected function clearCache()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Naozaj chcete vymazať všetku cache?')) {
                $this->info('Operácia zrušená.');
                return 0;
            }
        }

        $this->info('Mažem inventory cache...');
        
        try {
            $this->cacheService->forgetAll();
            $this->info('✅ Cache bola úspešne vymazaná.');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Chyba pri mazaní cache: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Warm up the cache
     */
    protected function warmUpCache()
    {
        $this->info('Predhrievam cache...');
        
        try {
            $this->cacheService->warmUp();
            $this->info('✅ Cache bola úspešne predhriatá.');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Chyba pri predhriatí cache: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show cache information
     */
    protected function showCacheInfo()
    {
        $this->info('📊 Informácie o cache:');
        $this->newLine();

        try {
            $info = $this->cacheService->getCacheInfo();
            
            $headers = ['Typ dát', 'Cache kľúč', 'Existuje', 'Počet záznamov'];
            $rows = [];

            foreach ($info as $name => $data) {
                $rows[] = [
                    ucfirst(str_replace('_', ' ', $name)),
                    $data['key'],
                    $data['exists'] ? '✅ Áno' : '❌ Nie',
                    $data['data_count']
                ];
            }

            $this->table($headers, $rows);
            
            // Celkové štatistiky
            $existingCount = count(array_filter($info, fn($item) => $item['exists']));
            $totalCount = count($info);
            
            $this->newLine();
            $this->info("📈 Celkový prehľad:");
            $this->info("   Aktívnych cache: {$existingCount}/{$totalCount}");
            $this->info("   Pokrytie: " . round(($existingCount / $totalCount) * 100, 1) . "%");

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Chyba pri získavaní informácií o cache: ' . $e->getMessage());
            return 1;
        }
    }
}