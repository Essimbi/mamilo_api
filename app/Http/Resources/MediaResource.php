<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (!$this->resource) {
            return [];
        }


        return [
            'id' => $this->id,
            'url' => $this->path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->path) : null,
            'thumbnailUrl' => $this->thumbnail_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->thumbnail_path) : null,
            'filename' => $this->file_name,
            'mimeType' => $this->mime_type,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'altText' => $this->alt_text,
            'caption' => $this->caption,
        ];
    }
}
