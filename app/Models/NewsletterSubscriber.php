<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "NewsletterSubscriber",
    required: ["email", "unsubscribe_token"],
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "email", type: "string", format: "email"),
        new OA\Property(property: "is_active", type: "boolean"),
        new OA\Property(property: "subscribed_at", type: "string", format: "date-time")
    ]
)]
class NewsletterSubscriber extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'email',
        'is_active',
        'unsubscribe_token',
        'subscribed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscribed_at' => 'datetime',
    ];

    // ==================== SCOPES ====================

    /**
     * Scope: Active subscribers only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Inactive subscribers
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Recent subscribers
     */
    public function scopeRecent($query)
    {
        return $query->latest('subscribed_at');
    }

    /**
     * Scope: Search by email
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('email', 'like', "%{$term}%");
    }
}
