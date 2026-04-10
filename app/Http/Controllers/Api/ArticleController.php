<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleListResource;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class ArticleController extends BaseController
{
    protected ContentService $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/api/v1/articles",
        summary: "List all articles",
        tags: ["Articles"],
        parameters: [
            new OA\Parameter(name: "category", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "tag", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 20))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Article")),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "meta", type: "object", properties: [
                            new OA\Property(property: "total", type: "integer")
                        ])
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Article::with(['author', 'categories', 'tags', 'coverImage'])
            ->when($request->category, function ($q) use ($request) {
                $q->whereHas('categories', fn($c) => $c->where('slug', $request->category));
            })
            ->when($request->tag, function ($q) use ($request) {
                $q->whereHas('tags', fn($t) => $t->where('slug', $request->tag));
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            })
            ->latest();

        if (!auth()->check() || auth()->user()->role !== 'admin') {
            $query->where('status', 'published');
        }

        $cacheKey = 'articles_index_' . md5(serialize($request->all()) . (auth()->check() ? auth()->user()->role : 'guest'));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($query, $request) {
            $articles = $query->paginate($request->limit ?? 20);
            return $this->sendResponse(
                ArticleListResource::collection($articles),
                'Articles récupérés.',
                ['total' => $articles->total()]
            );
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/api/v1/admin/articles",
        summary: "Create a new article (Admin)",
        tags: ["Admin Articles"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Article")
        ),
        responses: [
            new OA\Response(response: 201, description: "Article created successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function store(StoreArticleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['author_id'] = auth()->id();
        
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->contentService->generateUniqueSlug($data['title'], Article::class);
        }
        
        // Calculate reading time from blocks if provided
        if (isset($data['blocks']) && !empty($data['blocks'])) {
            $data['reading_time'] = $this->contentService->calculateReadingTimeFromBlocks($data['blocks']);
        }
        
        $article = Article::create($data);

        // Blocks
        if (isset($data['blocks'])) {
            foreach ($data['blocks'] as $block) {
                // Sanitize HTML content in blocks
                if (isset($block['content']) && is_string($block['content'])) {
                    $block['content'] = $this->contentService->sanitizeHtml($block['content']);
                }
                $article->blocks()->create($block);
            }
        }

        \Illuminate\Support\Facades\Cache::flush();

        // Relations
        if (isset($data['category_ids'])) {
            $article->categories()->sync($data['category_ids']);
        }
        if (isset($data['tag_ids'])) {
            $article->tags()->sync($data['tag_ids']);
        }

        // SEO
        if (isset($data['seo'])) {
            $article->seo()->create($data['seo']);
        }

        return $this->sendResponse(new ArticleResource($article->load(['blocks', 'author', 'categories', 'tags', 'seo', 'coverImage'])), 'Article créé.', [], 201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/api/v1/articles/{slug}",
        summary: "Get article details",
        tags: ["Articles"],
        parameters: [
            new OA\Parameter(name: "slug", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/Article")
            ),
            new OA\Response(response: 404, description: "Article not found")
        ]
    )]
    public function show(string $slug): JsonResponse
    {
        $cacheKey = 'article_show_' . $slug;

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($slug) {
            $article = Article::with(['blocks', 'author', 'categories', 'tags', 'seo', 'coverImage'])
                ->where('slug', $slug)
                ->firstOrFail();

            return $this->sendResponse(new ArticleResource($article), 'Article récupéré.');
        });
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/api/v1/admin/articles/{id}",
        summary: "Update an article (Admin)",
        tags: ["Admin Articles"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Article")
        ),
        responses: [
            new OA\Response(response: 200, description: "Article updated successfully"),
            new OA\Response(response: 404, description: "Article not found")
        ]
    )]
    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $data = $request->validated();
        
        // Auto-generate slug if title changed and slug not provided
        if (isset($data['title']) && empty($data['slug']) && $data['title'] !== $article->title) {
            $data['slug'] = $this->contentService->generateUniqueSlug($data['title'], Article::class, $article->id);
        }
        
        // Update blocks if provided
        if (isset($data['blocks'])) {
            // Delete existing blocks
            $article->blocks()->delete();
            
            // Create new blocks
            foreach ($data['blocks'] as $block) {
                // Sanitize HTML content in blocks
                if (isset($block['content']) && is_string($block['content'])) {
                    $block['content'] = $this->contentService->sanitizeHtml($block['content']);
                }
                $article->blocks()->create($block);
            }
            
            // Recalculate reading time
            $data['reading_time'] = $this->contentService->calculateReadingTimeFromBlocks($data['blocks']);
        }
        
        $article->update($data);

        // Update relations
        if (isset($data['category_ids'])) {
            $article->categories()->sync($data['category_ids']);
        }
        if (isset($data['tag_ids'])) {
            $article->tags()->sync($data['tag_ids']);
        }

        // SEO update
        if (isset($data['seo'])) {
            $article->seo()->updateOrCreate(['model_id' => $article->id, 'model_type' => Article::class], $data['seo']);
        }

        \Illuminate\Support\Facades\Cache::flush();
        
        return $this->sendResponse(new ArticleResource($article->load(['blocks', 'author', 'categories', 'tags', 'seo', 'coverImage'])), 'Article mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/v1/admin/articles/{id}",
        summary: "Delete an article (Admin)",
        tags: ["Admin Articles"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Article deleted successfully"),
            new OA\Response(response: 404, description: "Article not found")
        ]
    )]
    public function destroy(Article $article): JsonResponse
    {
        $article->delete();
        \Illuminate\Support\Facades\Cache::flush();
        return $this->sendResponse([], 'Article supprimé (corbeille).');
    }

    /**
     * Increment likes count for an article.
     */
    #[OA\Post(
        path: "/api/v1/articles/{id}/like",
        summary: "Like an article",
        tags: ["Articles"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Article liked successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "likesCount", type: "integer")
                        ])
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Article not found")
        ]
    )]
    public function like(string $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $article->increment('likes_count');
        
        // Invalidate cache
        \Illuminate\Support\Facades\Cache::forget('article_show_' . $article->slug);
        
        return $this->sendResponse(['likesCount' => $article->likes_count], 'Article liké avec succès.');
    }

    #[OA\Delete(
        path: "/api/v1/articles/{id}/like",
        summary: "Remove like from an article",
        tags: ["Articles"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Like removed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "likesCount", type: "integer")
                        ])
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Article not found")
        ]
    )]
    public function unlike(string $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $article->decrement('likes_count');
        
        // Invalidate cache
        \Illuminate\Support\Facades\Cache::forget('article_show_' . $article->slug);
        
        return $this->sendResponse(['likesCount' => $article->likes_count], 'Like retiré avec succès.');
    }
}
