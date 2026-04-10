<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Polices will handle specific access
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:articles,slug',
            'excerpt' => 'nullable|string',
            'cover_image_id' => 'nullable|exists:media,id',
            'status' => 'required|in:draft,review,scheduled,published,archived',
            'published_at' => 'nullable|date',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'blocks' => 'nullable|array',
            'blocks.*.type' => 'required|in:paragraph,heading,image,gallery,quote',
            'blocks.*.content' => 'required|array',
            'blocks.*.position' => 'required|integer',
            'seo' => 'nullable|array',
            'seo.meta_title' => 'nullable|string|max:255',
            'seo.meta_description' => 'nullable|string',
            'seo.og_image_id' => 'nullable|exists:media,id',
            'seo.canonical_url' => 'nullable|url',
        ];
    }
}
