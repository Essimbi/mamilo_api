<?php

namespace App\Console\Commands;

use App\Models\Comment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command: Clear old pending comments
 * 
 * Supprime les commentaires en attente de modération depuis plus de N jours.
 * Utilisation: php artisan comments:clear-pending --days=30
 */
class ClearPendingComments extends Command
{
    protected $signature = 'comments:clear-pending {--days=30 : Nombre de jours avant suppression}';

    protected $description = 'Delete pending comments older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days');
        $this->info("Clearing pending comments older than {$days} days...");

        $deleted = Comment::where('is_approved', false)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Deleted {$deleted} pending comments.");
        Log::info("Cleared {$deleted} pending comments via command");

        return 0;
    }
}
