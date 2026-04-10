<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class EventService
{
    protected ContentService $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Get upcoming events
     */
    public function getUpcoming(int $limit = 20): Collection
    {
        return Event::upcoming()
            ->with(['coverImage', 'seo', 'comments' => function ($q) {
                $q->approved();
            }])
            ->limit($limit)
            ->get();
    }

    /**
     * Get past events
     */
    public function getPast(int $limit = 20): Collection
    {
        return Event::past()
            ->with(['coverImage', 'seo', 'comments' => function ($q) {
                $q->approved();
            }])
            ->limit($limit)
            ->get();
    }

    /**
     * Get event by slug
     */
    public function getBySlug(string $slug): ?Event
    {
        $cacheKey = "event_slug_{$slug}";

        return Cache::remember($cacheKey, 86400, function () use ($slug) {
            return Event::active()
                ->with(['coverImage', 'seo', 'recapArticle', 'comments' => function ($q) {
                    $q->approved();
                }])
                ->where('slug', $slug)
                ->firstOrFail();
        });
    }

    /**
     * Create event
     */
    public function create(array $data): Event
    {
        // Generate unique slug
        $data['slug'] = $this->contentService->generateUniqueSlug($data['title'], Event::class);

        // Auto-publish if status is published and no event_date
        if ($data['status'] === 'published' && !isset($data['status'])) {
            // event_date is required, so just set status
        }

        // Create event
        $event = Event::create($data);

        // Create SEO meta if provided
        if (isset($data['seo'])) {
            $event->seo()->create($data['seo']);
        }

        Cache::flush();

        return $event->load(['coverImage', 'seo', 'recapArticle', 'comments']);
    }

    /**
     * Update event
     */
    public function update(Event $event, array $data): Event
    {
        // Update slug if title changed
        if (isset($data['title']) && $data['title'] !== $event->title) {
            $data['slug'] = $this->contentService->generateUniqueSlug($data['title'], Event::class, $event->id);
        }

        // Update event
        $event->update($data);

        // Update SEO if provided
        if (isset($data['seo'])) {
            $event->seo()->updateOrCreate([], $data['seo']);
        }

        Cache::forget("event_slug_{$event->slug}");

        return $event->load(['coverImage', 'seo', 'recapArticle', 'comments']);
    }

    /**
     * Delete event
     */
    public function delete(Event $event): bool
    {
        Cache::flush();
        return $event->delete();
    }

    /**
     * Like event
     */
    public function like(Event $event): int
    {
        $event->increment('likes_count');
        Cache::forget("event_slug_{$event->slug}");
        return $event->likes_count;
    }

    /**
     * Unlike event
     */
    public function unlike(Event $event): int
    {
        $event->decrement('likes_count');
        Cache::forget("event_slug_{$event->slug}");
        return $event->likes_count;
    }
}
