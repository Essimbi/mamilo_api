<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TagController extends BaseController
{
    #[OA\Get(
        path: "/api/v1/tags",
        summary: "List all tags",
        tags: ["Tags"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Tag")),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        return $this->sendResponse(TagResource::collection(Tag::all()), 'Tags récupérés.');
    }

    #[OA\Get(
        path: "/api/v1/tags/{slug}",
        summary: "Get tag by slug",
        tags: ["Tags"],
        parameters: [
            new OA\Parameter(name: "slug", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Tag not found")
        ]
    )]
    public function show(string $slug): JsonResponse
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();
        return $this->sendResponse(new TagResource($tag), 'Tag récupéré.');
    }

    #[OA\Post(
        path: "/api/v1/admin/tags",
        summary: "Create a new tag (Admin)",
        tags: ["Admin Tags"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "slug"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Laravel"),
                    new OA\Property(property: "slug", type: "string", example: "laravel")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Tag created successfully"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string', 'slug' => 'required|unique:tags,slug']);
        $tag = Tag::create($request->all());
        return $this->sendResponse(new TagResource($tag), 'Tag créé.', [], 201);
    }

    #[OA\Put(
        path: "/api/v1/admin/tags/{id}",
        summary: "Update a tag (Admin)",
        tags: ["Admin Tags"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "slug", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Tag updated successfully"),
            new OA\Response(response: 404, description: "Tag not found")
        ]
    )]
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|unique:tags,slug,' . $tag->id
        ]);
        $tag->update($request->all());
        return $this->sendResponse(new TagResource($tag), 'Tag mis à jour.');
    }

    #[OA\Delete(
        path: "/api/v1/admin/tags/{id}",
        summary: "Delete a tag (Admin)",
        tags: ["Admin Tags"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Tag deleted successfully"),
            new OA\Response(response: 404, description: "Tag not found")
        ]
    )]
    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();
        return $this->sendResponse([], 'Tag supprimé.');
    }
}
