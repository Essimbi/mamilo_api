<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    /**
     * Get all tags with article count
     */
    public function getAll(): Collection
    {
        return Tag::popular()->get();
    }

    /**
     * Get tag by slug
     */
    public function getBySlug(string $slug): ?Tag
    {
        return Tag::where('slug', $slug)->firstOrFail();
    }

    /**
     * Create tag
     */
    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    /**
     * Update tag
     */
    public function update(Tag $tag, array $data): Tag
    {
        $tag->update($data);
        return $tag;
    }

    /**
     * Delete tag
     */
    public function delete(Tag $tag): bool
    {
        return $tag->delete();
    }

    /**
     * Search tags
     */
    public function search(string $term): Collection
    {
        return Tag::search($term)->get();
    }
}
