<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command: Publish scheduled articles
 * 
 * Publie tous les articles programmés dont la date est dépassée.
 * Utilisation: php artisan articles:publish-scheduled
 */
class PublishScheduledArticles extends Command
{
    protected $signature = 'articles:publish-scheduled';

    protected $description = 'Publish all scheduled articles that are ready';

    protected ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        parent::__construct();
        $this->articleService = $articleService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Publishing scheduled articles...');

        $articles = Article::where('status', 'scheduled')
            ->where('published_at', '<=', now())
            ->get();

        if ($articles->isEmpty()) {
            $this->info('No scheduled articles to publish.');
            return 0;
        }

        foreach ($articles as $article) {
            try {
                $this->articleService->update($article, ['status' => 'published']);
                $this->line("✓ Published: {$article->title}");
                Log::info("Article published via command: {$article->id}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to publish: {$article->title}");
                Log::error("Failed to publish article: {$e->getMessage()}");
            }
        }

        $this->info("Total published: {$articles->count()}");

        return 0;
    }
}
