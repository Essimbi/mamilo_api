<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MediaController extends BaseController
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/api/v1/admin/media",
        summary: "List all media with filtering and search (Admin)",
        tags: ["Admin Media"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 20)),
            new OA\Parameter(name: "type", in: "query", schema: new OA\Schema(type: "string", enum: ["image", "video", "document"]), description: "Filter by media type"),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string"), description: "Search by filename")
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Media")),
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
        $query = Media::query();

        // Filtrer par type si fourni
        if ($request->has('type') && $request->type) {
            $query = $this->filterByType($query, $request->type);
        }

        // Recherche par nom/filename si fournie
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where('file_name', 'like', "%{$searchTerm}%")
                  ->orWhere('name', 'like', "%{$searchTerm}%");
        }

        $media = $query->latest()->paginate($request->limit ?? 20);

        return $this->sendResponse(MediaResource::collection($media), 'Médias récupérés.', ['total' => $media->total()]);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/api/v1/admin/media/{id}",
        summary: "Get media details (Admin)",
        tags: ["Admin Media"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Media details",
                content: new OA\JsonContent(ref: "#/components/schemas/Media")
            ),
            new OA\Response(response: 404, description: "Media not found")
        ]
    )]
    public function show(Media $media): JsonResponse
    {
        return $this->sendResponse(new MediaResource($media), 'Détails du média récupérés.');
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/api/v1/admin/media/upload",
        summary: "Upload a new media file (Admin)",
        tags: ["Admin Media"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["file"],
                    properties: [
                        new OA\Property(property: "file", type: "string", format: "binary", description: "Media file (Max 10MB)"),
                        new OA\Property(property: "alt", type: "string", description: "Alternative text"),
                        new OA\Property(property: "description", type: "string", description: "Media description")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "File uploaded successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/Media")
            ),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Upload error")
        ]
    )]
    public function upload(StoreMediaRequest $request): JsonResponse
    {
        try {
            $media = $this->mediaService->processUpload(
                $request->file('file'),
                $request->input('alt'),
                $request->input('description')
            );

            return $this->sendResponse(new MediaResource($media), 'Fichier uploadé avec succès.', [], 201);
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de l\'upload du fichier: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/api/v1/admin/media/{id}",
        summary: "Update media metadata (Admin)",
        tags: ["Admin Media"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "alt", type: "string", nullable: true),
                    new OA\Property(property: "description", type: "string", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Media updated successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/Media")
            ),
            new OA\Response(response: 404, description: "Media not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function update(Media $media, UpdateMediaRequest $request): JsonResponse
    {
        try {
            $media->update([
                'alt_text' => $request->input('alt'),
                'caption' => $request->input('description'),
            ]);

            return $this->sendResponse(new MediaResource($media), 'Métadonnées du média mises à jour.');
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de la mise à jour: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/v1/admin/media/{id}",
        summary: "Delete a media file (Admin)",
        tags: ["Admin Media"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "File deleted successfully"),
            new OA\Response(response: 404, description: "Media not found")
        ]
    )]
    public function destroy(Media $media): JsonResponse
    {
        try {
            $this->mediaService->deleteMedia($media);
            return $this->sendResponse([], 'Fichier supprimé avec succès.');
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de la suppression: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Filter media by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function filterByType($query, string $type)
    {
        return match($type) {
            'image' => $query->where('mime_type', 'like', 'image/%'),
            'video' => $query->where('mime_type', 'like', 'video/%'),
            'document' => $query->whereIn('mime_type', [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'application/zip',
            ]),
            default => $query,
        };
    }
}
