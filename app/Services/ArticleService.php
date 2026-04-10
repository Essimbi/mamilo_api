<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class ArticleService
{
    protected ContentService $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Get published articles with optional filters
     */
    public function getPublished(?string $category = null, ?string $tag = null, ?string $search = null, int $limit = 20): Collection
    {
        $query = Article::published();

        if ($category) {
            $query->byCategory($category);
        }

        if ($tag) {
            $query->byTag($tag);
        }

        if ($search) {
            $query->search($search);
        }

        return $query->with(['author', 'categories', 'tags', 'coverImage'])
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get article by slug with relations
     */
    public function getBySlug(string $slug): ?Article
    {
        $cacheKey = "article_slug_{$slug}";

        return Cache::remember($cacheKey, 86400, function () use ($slug) {
            return Article::published()
                ->with(['author', 'blocks', 'categories', 'tags', 'seo', 'coverImage', 'comments' => function ($q) {
                    $q->approved();
                }])
                ->where('slug', $slug)
                ->firstOrFail();
        });
    }

    /**
     * Create article with blocks, relations, and SEO
     */
    public function create(array $data): Article
    {
        // Generate unique slug
        $data['slug'] = $this->contentService->generateUniqueSlug($data['title'], Article::class);

        // Auto-publish if status is published and no published_at
        if ($data['status'] === 'published' && !isset($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Create article
        $article = Article::create($data);

        // Create blocks
        if (isset($data['blocks']) && is_array($data['blocks'])) {
            foreach ($data['blocks'] as $block) {
                if (isset($block['content']) && is_string($block['content'])) {
                    $block['content'] = $this->contentService->sanitizeHtml($block['content']);
                }
                $article->blocks()->create($block);
            }
        }

        // Sync relations
        if (isset($data['category_ids'])) {
            $article->categories()->sync($data['category_ids']);
        }

        if (isset($data['tag_ids'])) {
            $article->tags()->sync($data['tag_ids']);
        }

        // Create SEO meta
        if (isset($data['seo'])) {
            $article->seo()->create($data['seo']);
        }

        // Clear cache
        Cache::flush();

        return $article->load(['blocks', 'author', 'categories', 'tags', 'seo', 'coverImage']);
    }

    /**
     * Update article with blocks and relations
     */
    public function update(Article $article, array $data): Article
    {
        // Update slug if title changed
        if (isset($data['title']) && $data['title'] !== $article->title) {
            $data['slug'] = $this->contentService->generateUniqueSlug($data['title'], Article::class, $article->id);
        }

        // Update article
        $article->update($data);

        // Update blocks if provided
        if (isset($data['blocks']) && is_array($data['blocks'])) {
            $article->blocks()->delete();

            foreach ($data['blocks'] as $block) {
                if (isset($block['content']) && is_string($block['content'])) {
                    $block['content'] = $this->contentService->sanitizeHtml($block['content']);
                }
                $article->blocks()->create($block);
            }
        }

        // Sync relations
        if (isset($data['category_ids'])) {
            $article->categories()->sync($data['category_ids']);
        }

        if (isset($data['tag_ids'])) {
            $article->tags()->sync($data['tag_ids']);
        }

        // Update SEO
        if (isset($data['seo'])) {
            $article->seo()->updateOrCreate([], $data['seo']);
        }

        // Clear cache
        Cache::flush();

        return $article->load(['blocks', 'author', 'categories', 'tags', 'seo', 'coverImage']);
    }

    /**
     * Delete article and related data
     */
    public function delete(Article $article): bool
    {
        Cache::flush();
        return $article->delete();
    }

    /**
     * Like article
     */
    public function like(Article $article): int
    {
        $article->increment('likes_count');
        Cache::forget("article_slug_{$article->slug}");
        return $article->likes_count;
    }

    /**
     * Unlike article
     */
    public function unlike(Article $article): int
    {
        $article->decrement('likes_count');
        Cache::forget("article_slug_{$article->slug}");
        return $article->likes_count;
    }
}
