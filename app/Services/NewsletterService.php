<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class NewsletterService
{
    /**
     * Subscribe email to newsletter
     */
    public function subscribe(string $email): NewsletterSubscriber
    {
        return NewsletterSubscriber::updateOrCreate(
            ['email' => $email],
            [
                'is_active' => true,
                'unsubscribe_token' => Str::random(32),
                'subscribed_at' => now(),
            ]
        );
    }

    /**
     * Unsubscribe email from newsletter
     */
    public function unsubscribe(string $email): bool
    {
        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if (!$subscriber) {
            return false;
        }

        return $subscriber->update(['is_active' => false]);
    }

    /**
     * Unsubscribe by token
     */
    public function unsubscribeByToken(string $token): bool
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if (!$subscriber) {
            return false;
        }

        return $subscriber->update(['is_active' => false]);
    }

    /**
     * Get active subscribers
     */
    public function getActive(): Collection
    {
        return NewsletterSubscriber::active()->recent()->get();
    }

    /**
     * Get all subscribers with count
     */
    public function getAll(): Collection
    {
        return NewsletterSubscriber::recent()->get();
    }

    /**
     * Search subscribers
     */
    public function search(string $term): Collection
    {
        return NewsletterSubscriber::search($term)->get();
    }

    /**
     * Check if email is subscribed
     */
    public function isSubscribed(string $email): bool
    {
        return NewsletterSubscriber::where('email', $email)->where('is_active', true)->exists();
    }
}
