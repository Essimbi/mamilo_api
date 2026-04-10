<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Event;
use App\Models\User;
use App\Models\NewsletterSubscriber;
use Illuminate\Console\Command;

/**
 * Command: Display API statistics
 * 
 * Affiche des statistiques sur le blog.
 * Utilisation: php artisan stats:show
 */
class ShowStats extends Command
{
    protected $signature = 'stats:show';

    protected $description = 'Display blog statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📊 Blog Statistics');
        $this->line(str_repeat('=', 50));

        // Articles
        $articlesCount = Article::count();
        $publishedCount = Article::published()->count();
        $totalLikes = Article::sum('likes_count');

        $this->line("\n📝 Articles:");
        $this->line("  Total: {$articlesCount}");
        $this->line("  Published: {$publishedCount}");
        $this->line("  Draft: " . ($articlesCount - $publishedCount));
        $this->line("  Total Likes: {$totalLikes}");

        // Events
        $eventsCount = Event::count();
        $upcomingCount = Event::upcoming()->count();
        $eventLikes = Event::sum('likes_count');

        $this->line("\n📅 Events:");
        $this->line("  Total: {$eventsCount}");
        $this->line("  Upcoming: {$upcomingCount}");
        $this->line("  Past: " . ($eventsCount - $upcomingCount));
        $this->line("  Total Likes: {$eventLikes}");

        // Comments
        $commentsCount = Comment::count();
        $approvedCount = Comment::approved()->count();
        $pendingCount = Comment::pending()->count();

        $this->line("\n💬 Comments:");
        $this->line("  Total: {$commentsCount}");
        $this->line("  Approved: {$approvedCount}");
        $this->line("  Pending: {$pendingCount}");

        // Users
        $usersCount = User::count();
        $adminsCount = User::admins()->count();
        $editorsCount = User::editors()->count();

        $this->line("\n👥 Users:");
        $this->line("  Total: {$usersCount}");
        $this->line("  Admins: {$adminsCount}");
        $this->line("  Editors: {$editorsCount}");

        // Newsletter
        $subscribersCount = NewsletterSubscriber::active()->count();
        $totalSubscribed = NewsletterSubscriber::count();

        $this->line("\n📧 Newsletter:");
        $this->line("  Active: {$subscribersCount}");
        $this->line("  Total: {$totalSubscribed}");

        $this->line("\n" . str_repeat('=', 50));

        return 0;
    }
}
