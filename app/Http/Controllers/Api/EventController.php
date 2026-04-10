<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class EventController extends BaseController
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
        path: "/api/v1/events",
        summary: "List all events",
        tags: ["Events"],
        parameters: [
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 10))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Event")),
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
        $cacheKey = 'events_index_' . md5(serialize($request->all()));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($request) {
            $events = Event::with(['coverImage'])
                ->latest('event_date')
                ->paginate($request->limit ?? 10);

            return $this->sendResponse(EventResource::collection($events), 'Événements récupérés.', ['total' => $events->total()]);
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/api/v1/admin/events",
        summary: "Create a new event (Admin)",
        tags: ["Admin Events"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Event")
        ),
        responses: [
            new OA\Response(response: 201, description: "Event created successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function store(StoreEventRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->contentService->generateUniqueSlug($data['title'], Event::class);
        }
        
        // Sanitize description
        if (isset($data['description'])) {
            $data['description'] = $this->contentService->sanitizeHtml($data['description']);
        }
        
        $event = Event::create($data);

        if (isset($data['seo'])) {
            $event->seo()->create($data['seo']);
        }

        \Illuminate\Support\Facades\Cache::flush();

        return $this->sendResponse(new EventResource($event->load(['coverImage', 'seo'])), 'Événement créé.', [], 201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/api/v1/events/{slug}",
        summary: "Get event details",
        tags: ["Events"],
        parameters: [
            new OA\Parameter(name: "slug", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/Event")
            ),
            new OA\Response(response: 404, description: "Event not found")
        ]
    )]
    public function show(string $slug): JsonResponse
    {
        $cacheKey = 'event_show_' . $slug;

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($slug) {
            $event = Event::with(['coverImage', 'seo', 'recapArticle'])
                ->where('slug', $slug)
                ->firstOrFail();

            return $this->sendResponse(new EventResource($event), 'Événement récupéré.');
        });
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/api/v1/admin/events/{id}",
        summary: "Update an event (Admin)",
        tags: ["Admin Events"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/Event")
        ),
        responses: [
            new OA\Response(response: 200, description: "Event updated successfully"),
            new OA\Response(response: 404, description: "Event not found")
        ]
    )]
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $data = $request->validated();
        
        // Auto-generate slug if title changed and slug not provided
        if (isset($data['title']) && empty($data['slug']) && $data['title'] !== $event->title) {
            $data['slug'] = $this->contentService->generateUniqueSlug($data['title'], Event::class, $event->id);
        }
        
        // Sanitize description
        if (isset($data['description'])) {
            $data['description'] = $this->contentService->sanitizeHtml($data['description']);
        }
        
        $event->update($data);

        if (isset($data['seo'])) {
            $event->seo()->updateOrCreate(['model_id' => $event->id, 'model_type' => Event::class], $data['seo']);
        }

        \Illuminate\Support\Facades\Cache::flush();

        return $this->sendResponse(new EventResource($event->load(['coverImage', 'seo'])), 'Événement mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/v1/admin/events/{id}",
        summary: "Delete an event (Admin)",
        tags: ["Admin Events"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Event deleted successfully"),
            new OA\Response(response: 404, description: "Event not found")
        ]
    )]
    public function destroy(Event $event): JsonResponse
    {
        $event->delete();
        \Illuminate\Support\Facades\Cache::flush();
        return $this->sendResponse([], 'Événement supprimé.');
    }

    /**
     * Increment likes count for an event.
     */
    #[OA\Post(
        path: "/api/v1/events/{id}/like",
        summary: "Like an event",
        tags: ["Events"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Event liked successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "likesCount", type: "integer")
                        ])
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Event not found")
        ]
    )]
    public function like(string $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $event->increment('likes_count');
        
        // Invalidate cache
        \Illuminate\Support\Facades\Cache::forget('event_show_' . $event->slug);
        
        return $this->sendResponse(['likesCount' => $event->likes_count], 'Événement liké avec succès.');
    }

    #[OA\Delete(
        path: "/api/v1/events/{id}/like",
        summary: "Remove like from an event",
        tags: ["Events"],
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
            new OA\Response(response: 404, description: "Event not found")
        ]
    )]
    public function unlike(string $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $event->decrement('likes_count');
        
        // Invalidate cache
        \Illuminate\Support\Facades\Cache::forget('event_show_' . $event->slug);
        
        return $this->sendResponse(['likesCount' => $event->likes_count], 'Like retiré avec succès.');
    }
}
