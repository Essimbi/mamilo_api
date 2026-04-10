<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'bio' => $this->bio,
            'avatar' => new MediaResource($this->avatar_id ? \App\Models\Media::find($this->avatar_id) : null), // Spatie fallback or FK
            'createdAt' => $this->created_at->toISOString(),
        ];
    }
}
