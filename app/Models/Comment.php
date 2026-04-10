<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Comment",
    required: ["commentable_type", "commentable_id", "author_name", "content"],
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "commentable_type", type: "string"),
        new OA\Property(property: "commentable_id", type: "string", format: "uuid"),
        new OA\Property(property: "author_name", type: "string"),
        new OA\Property(property: "author_avatar", type: "string", nullable: true),
        new OA\Property(property: "content", type: "string"),
        new OA\Property(property: "is_approved", type: "boolean"),
        new OA\Property(property: "created_at", type: "string", format: "date-time")
    ]
)]
class Comment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'author_name',
        'author_avatar',
        'content',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * Get the parent commentable model.
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Approved comments only
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope: Pending comments
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope: Recent comments
     */
    public function scopeRecent($query)
    {
        return $query->latest('created_at');
    }

    /**
     * Scope: Search by author or content
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('author_name', 'like', "%{$term}%")
            ->orWhere('content', 'like', "%{$term}%");
    }
}
