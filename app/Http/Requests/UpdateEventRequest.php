<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|unique:events,slug,' . ($this->event->id ?? ''),
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'event_date' => 'nullable|date',
            'type' => 'nullable|string|max:255',
            'cover_image_id' => 'nullable|exists:media,id',
            'recap_article_id' => 'nullable|exists:articles,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'exists:media,id',
        ];
    }
}
