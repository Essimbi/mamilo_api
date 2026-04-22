<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Models\Event;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\EventResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SearchController extends BaseController
{
    /**
     * Search across articles and events.
     */
    #[OA\Get(
        path: "/api/v1/search",
        summary: "Search across articles and events",
        tags: ["Search"],
        parameters: [
            new OA\Parameter(
                name: "q",
                in: "query",
                description: "Search query string",
                required: true,
                schema: new OA\Schema(type: "string", minLength: 2)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "articles", type: "array", items: new OA\Items(ref: "#/components/schemas/Article")),
                                new OA\Property(property: "events", type: "array", items: new OA\Items(ref: "#/components/schemas/Event"))
                            ]
                        ),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('q');

        if (!$query || strlen($query) < 2) {
            return $this->sendError('La requête de recherche doit contenir au moins 2 caractères.', [], 422);
        }

        $articles = Article::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%")
                  ->orWhereHas('categories', function($query_cat) use ($query) {
                      $query_cat->where('name', 'like', "%{$query}%");
                  })
                  ->orWhereHas('tags', function($query_tag) use ($query) {
                      $query_tag->where('name', 'like', "%{$query}%");
                  });
            })
            ->with(['author', 'categories', 'tags', 'coverImage'])
            ->latest('published_at')
            ->limit(10)
            ->get();

        $events = Event::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhere('event_location', 'like', "%{$query}%")
            ->latest('event_date')
            ->limit(10)
            ->get();

        return $this->sendResponse([
            'articles' => ArticleResource::collection($articles),
            'events' => EventResource::collection($events),
        ], 'Résultats de recherche récupérés avec succès.');
    }
}
