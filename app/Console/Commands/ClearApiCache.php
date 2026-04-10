<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Command: Clear API cache
 * 
 * Vide tous les caches de l'API (articles, événements, etc).
 * Utilisation: php artisan cache:clear-api
 */
class ClearApiCache extends Command
{
    protected $signature = 'cache:clear-api';

    protected $description = 'Clear all API-related caches';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Clearing API caches...');

        try {
            Cache::flush();
            $this->info('✓ All caches cleared successfully.');
            Log::info('API caches cleared via command');

            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Failed to clear caches: {$e->getMessage()}");
            Log::error("Failed to clear caches: {$e->getMessage()}");

            return 1;
        }
    }
}
