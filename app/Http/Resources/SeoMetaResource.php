<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeoMetaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'metaTitle' => $this->meta_title,
            'metaDescription' => $this->meta_description,
            'ogImage' => new MediaResource($this->ogImage),
            'canonicalUrl' => $this->canonical_url,
        ];
    }
}
