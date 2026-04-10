<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use OpenApi\Attributes as OA;

 #[OA\Schema(
     schema: "Tag",
     required: ["name", "slug"],
     properties: [
         new OA\Property(property: "id", type: "string", format: "uuid"),
         new OA\Property(property: "name", type: "string"),
         new OA\Property(property: "slug", type: "string")
     ]
 )]
 class Tag extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'slug'];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Popular tags (sorted by article count)
     */
    public function scopePopular($query)
    {
        return $query->withCount('articles')->orderBy('articles_count', 'desc');
    }

    /**
     * Scope: Search by name or slug
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('slug', 'like', "%{$term}%");
    }
}
