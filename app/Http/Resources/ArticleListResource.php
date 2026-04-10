<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'publishedAt' => $this->published_at ? $this->published_at->toISOString() : null,
            'readingTime' => $this->reading_time,
            'coverImage' => new MediaResource($this->coverImage),
            'author' => new UserResource($this->author),
            'categories' => CategoryResource::collection($this->categories),
            'tags' => TagResource::collection($this->tags),
        ];
    }
}
