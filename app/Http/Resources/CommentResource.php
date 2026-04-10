<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->article_id,
            'author_name' => $this->author_name,
            'author_avatar' => $this->author_avatar,
            'content' => $this->content,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
