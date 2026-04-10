<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Attributes as OA;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


#[OA\Schema(
    schema: "User",
    required: ["name", "email"],
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "name", type: "string"),
        new OA\Property(property: "email", type: "string", format: "email"),
        new OA\Property(property: "role", type: "string", enum: ["admin", "editor"]),
        new OA\Property(property: "bio", type: "string", nullable: true),
        new OA\Property(property: "avatar_id", type: "string", format: "uuid", nullable: true)
    ]
)]
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasApiTokens, InteractsWithMedia;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Les attributs qui doivent être masqués pour les formats Array ou JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bio',
        'avatar_id',
    ];

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'avatar_id');
    }

    /**
     * Get the attributes that should be cast.

     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Admin users only
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope: Editors only
     */
    public function scopeEditors($query)
    {
        return $query->where('role', 'editor');
    }

    /**
     * Scope: Verified users
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope: Unverified users
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    /**
     * Scope: Search by name or email
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%");
    }

    // ==================== MUTATORS & ACCESSORS ====================

    /**
     * Get the user's profile URL
     */
    public function getProfileUrlAttribute(): string
    {
        return url("/profile/{$this->id}");
    }

    /**
     * Check if user is admin
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }
}

