<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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
            'blocks' => ContentBlockResource::collection($this->blocks),
            'seo' => new SeoMetaResource($this->seo),
            'likesCount' => $this->likes_count ?? 0,
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),
        ];
    }
}
