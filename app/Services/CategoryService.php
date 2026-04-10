<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Get all categories with article count
     */
    public function getAll(): Collection
    {
        return Category::popular()->get();
    }

    /**
     * Get category by slug
     */
    public function getBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)->firstOrFail();
    }

    /**
     * Create category
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update category
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category;
    }

    /**
     * Delete category
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    /**
     * Search categories
     */
    public function search(string $term): Collection
    {
        return Category::search($term)->get();
    }
}
