<?php

namespace App\Http\Controllers\Api;

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
        summary: "List all media (Admin)",
        tags: ["Admin Media"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 20))
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
        $media = Media::latest()->paginate($request->limit ?? 20);

        return $this->sendResponse(MediaResource::collection($media), 'Médias récupérés.', ['total' => $media->total()]);
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
                        new OA\Property(property: "file", type: "string", format: "binary"),
                        new OA\Property(property: "alt_text", type: "string"),
                        new OA\Property(property: "caption", type: "string")
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
            new OA\Response(response: 500, description: "Upload error")
        ]
    )]
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,webp,gif,svg|max:10240',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
        ]);

        try {
            $media = $this->mediaService->processUpload(
                $request->file('file'),
                $request->input('alt_text'),
                $request->input('caption')
            );

            return $this->sendResponse(new MediaResource($media), 'Fichier uploadé avec succès.', [], 201);
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de l\'upload du fichier: ' . $e->getMessage(), [], 500);
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
        $media->delete();
        return $this->sendResponse([], 'Fichier supprimé.');
    }
}
