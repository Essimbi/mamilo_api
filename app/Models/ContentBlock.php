<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use OpenApi\Attributes as OA;

 #[OA\Schema(
     schema: "ContentBlock",
     required: ["type", "position", "content"],
     properties: [
         new OA\Property(property: "id", type: "string", format: "uuid"),
         new OA\Property(property: "type", type: "string"),
         new OA\Property(property: "position", type: "integer"),
         new OA\Property(property: "content", type: "object")
     ]
 )]
 class ContentBlock extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'article_id',
        'type',
        'position',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
