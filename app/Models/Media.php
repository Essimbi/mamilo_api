<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

use OpenApi\Attributes as OA;

 #[OA\Schema(
     schema: "Media",
     properties: [
         new OA\Property(property: "id", type: "string", format: "uuid"),
         new OA\Property(property: "url", type: "string"),
         new OA\Property(property: "thumbnail_url", type: "string"),
         new OA\Property(property: "file_name", type: "string"),
         new OA\Property(property: "mime_type", type: "string"),
         new OA\Property(property: "size", type: "integer"),
         new OA\Property(property: "alt_text", type: "string", nullable: true)
     ]
 )]
 class Media extends BaseMedia
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = [
        'model_type',
        'model_id',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'conversions_disk',
        'size',
        'manipulations',
        'custom_properties',
        'generated_conversions',
        'responsive_images',
        'order_column',
        'width',
        'height',
        'alt_text',
        'caption',
        'path',
        'thumbnail_path',
    ];
}
