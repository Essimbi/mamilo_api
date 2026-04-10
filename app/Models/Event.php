<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Event",
    required: ["title", "event_date"],
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "slug", type: "string"),
        new OA\Property(property: "description", type: "string", nullable: true),
        new OA\Property(property: "event_date", type: "string", format: "date-time"),
        new OA\Property(property: "end_date", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "location", type: "string", nullable: true),
        new OA\Property(property: "type", type: "string"),
        new OA\Property(property: "status", type: "string"),
        new OA\Property(property: "likes_count", type: "integer")
    ]
)]
class Event extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'location',
        'event_date',
        'cover_image_id',
        'recap_article_id',
        'status',
        'type',
        'likes_count',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'status' => 'string',
    ];

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_image_id');
    }

    public function recapArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'recap_article_id');
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'model');
    }

    /**
     * Get the comments for the event.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>', now())->where('status', 'published')->orderBy('event_date', 'asc');
    }

    /**
     * Scope: Past events
     */
    public function scopePast($query)
    {
        return $query->where('event_date', '<', now())->orderBy('event_date', 'desc');
    }

    /**
     * Scope: Active events
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: Popular events (sorted by likes)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('likes_count', 'desc');
    }

    /**
     * Scope: Search by title or description
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('title', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%");
    }

    // ==================== MUTATORS & ACCESSORS ====================

    /**
     * Get the event URL
     */
    public function getUrlAttribute(): string
    {
        return url("/events/{$this->slug}");
    }

    /**
     * Get formatted event date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->event_date?->format('d/m/Y H:i') ?? 'N/A';
    }

    /**
     * Check if event is in the future
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->event_date > now();
    }
}

