<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:events,slug',
            'description' => 'required|string',
            'location' => 'required|string',
            'event_date' => 'required|date',
            'type' => 'required|string|max:255',
            'cover_image_id' => 'nullable|exists:media,id',
            'recap_article_id' => 'nullable|exists:articles,id',
            'seo' => 'nullable|array',
            'seo.meta_title' => 'nullable|string|max:255',
            'seo.meta_description' => 'nullable|string',
            'seo.og_image_id' => 'nullable|exists:media,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'exists:media,id',
        ];
    }
}
