<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CommentService
{
    /**
     * Get approved comments for article
     */
    public function getArticleComments(Article $article): Collection
    {
        return $article->comments()
            ->approved()
            ->recent()
            ->get();
    }

    /**
     * Get approved comments for event
     */
    public function getEventComments(Event $event): Collection
    {
        return $event->comments()
            ->approved()
            ->recent()
            ->get();
    }

    /**
     * Create comment on article
     */
    public function createForArticle(Article $article, array $data): Comment
    {
        $comment = $article->comments()->create([
            'author_name' => $data['author_name'],
            'author_avatar' => $data['author_avatar'] ?? null,
            'content' => $data['content'],
            'is_approved' => false, // Require moderation
        ]);

        Cache::forget("article_slug_{$article->slug}");

        return $comment;
    }

    /**
     * Create comment on event
     */
    public function createForEvent(Event $event, array $data): Comment
    {
        $comment = $event->comments()->create([
            'author_name' => $data['author_name'],
            'author_avatar' => $data['author_avatar'] ?? null,
            'content' => $data['content'],
            'is_approved' => false, // Require moderation
        ]);

        Cache::forget("event_slug_{$event->slug}");

        return $comment;
    }

    /**
     * Get all pending comments for moderation
     */
    public function getPending(): Collection
    {
        return Comment::pending()
            ->recent()
            ->get();
    }

    /**
     * Approve comment
     */
    public function approve(Comment $comment): Comment
    {
        $comment->update(['is_approved' => true]);

        // Invalidate cache
        if ($comment->commentable_type === Article::class) {
            $article = Article::find($comment->commentable_id);
            Cache::forget("article_slug_{$article->slug}");
        } elseif ($comment->commentable_type === Event::class) {
            $event = Event::find($comment->commentable_id);
            Cache::forget("event_slug_{$event->slug}");
        }

        return $comment;
    }

    /**
     * Reject/delete comment
     */
    public function reject(Comment $comment): bool
    {
        // Invalidate cache
        if ($comment->commentable_type === Article::class) {
            $article = Article::find($comment->commentable_id);
            Cache::forget("article_slug_{$article->slug}");
        } elseif ($comment->commentable_type === Event::class) {
            $event = Event::find($comment->commentable_id);
            Cache::forget("event_slug_{$event->slug}");
        }

        return $comment->delete();
    }
}
