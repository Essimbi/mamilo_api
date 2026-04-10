<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Article",
    required: ["title", "slug", "status"],
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "title", type: "string"),
        new OA\Property(property: "slug", type: "string"),
        new OA\Property(property: "excerpt", type: "string", nullable: true),
        new OA\Property(property: "status", type: "string"),
        new OA\Property(property: "published_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "reading_time", type: "integer", nullable: true),
        new OA\Property(property: "likes_count", type: "integer"),
        new OA\Property(property: "created_at", type: "string", format: "date-time")
    ]
)]
class Article extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'cover_image_id',
        'status',
        'published_at',
        'author_id',
        'reading_time',
        'likes_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_image_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(ContentBlock::class)->orderBy('position');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'model');
    }

    /**
     * Get the comments for the article.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Published articles only
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    /**
     * Scope: Draft articles
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft')->orWhereNull('published_at');
    }

    /**
     * Scope: Recent articles (last 30 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('published_at', '>=', now()->subDays(30))->orderBy('published_at', 'desc');
    }

    /**
     * Scope: Popular articles (sorted by likes)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('likes_count', 'desc');
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, string $categorySlug)
    {
        return $query->whereHas('categories', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    /**
     * Scope: By tag
     */
    public function scopeByTag($query, string $tagSlug)
    {
        return $query->whereHas('tags', function ($q) use ($tagSlug) {
            $q->where('slug', $tagSlug);
        });
    }

    /**
     * Scope: By author
     */
    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * Scope: Search by title or excerpt
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('title', 'like', "%{$term}%")
            ->orWhere('excerpt', 'like', "%{$term}%");
    }

    // ==================== MUTATORS & ACCESSORS ====================

    /**
     * Get the author's name with fallback
     */
    public function getAuthorNameAttribute(): string
    {
        return $this->author?->name ?? 'Anonymous';
    }

    /**
     * Get the article URL
     */
    public function getUrlAttribute(): string
    {
        return url("/articles/{$this->slug}");
    }

    /**
     * Get formatted reading time
     */
    public function getFormattedReadingTimeAttribute(): string
    {
        if (!$this->reading_time) {
            return 'N/A';
        }

        if ($this->reading_time < 60) {
            return "{$this->reading_time} min read";
        }

        $hours = floor($this->reading_time / 60);
        $minutes = $this->reading_time % 60;

        return "{$hours}h {$minutes}m read";
    }
}

