<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find scheduled articles that reached their publication time and publish them.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $count = Article::where('status', '=', 'scheduled')
            ->where('published_at', '<=', $now)
            ->update(['status' => 'published']);

        if ($count > 0) {
            $this->info("{$count} articles published successfully.");
        } else {
            $this->info('No articles to publish at this time.');
        }
    }
}
