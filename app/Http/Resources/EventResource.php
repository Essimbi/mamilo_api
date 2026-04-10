<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'location' => $this->location,
            'eventDate' => $this->event_date->toISOString(),
            'type' => $this->type,
            'status' => $this->status,
            'coverImage' => new MediaResource($this->coverImage),
            'recapArticle' => $this->recap_article_id ? [
                'id' => $this->recap_article_id,
                'slug' => $this->recapArticle->slug ?? null,
                'title' => $this->recapArticle->title ?? null,
            ] : null,
            'seo' => new SeoMetaResource($this->seo),
            'createdAt' => $this->created_at->toISOString(),
        ];
    }
}
