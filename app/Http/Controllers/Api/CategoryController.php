<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CategoryController extends BaseController
{
    #[OA\Get(
        path: "/api/v1/categories",
        summary: "List all categories",
        tags: ["Categories"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Category")),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        return $this->sendResponse(CategoryResource::collection(Category::all()), 'Catégories récupérées.');
    }

    #[OA\Get(
        path: "/api/v1/categories/{slug}",
        summary: "Get category by slug",
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "slug", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        return $this->sendResponse(new CategoryResource($category), 'Catégorie récupérée.');
    }

    #[OA\Post(
        path: "/api/v1/admin/categories",
        summary: "Create a new category (Admin)",
        tags: ["Admin Categories"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "slug"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Tech"),
                    new OA\Property(property: "slug", type: "string", example: "tech")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Category created successfully"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string', 'slug' => 'required|unique:categories,slug']);
        $category = Category::create($request->all());
        return $this->sendResponse(new CategoryResource($category), 'Catégorie créée.', [], 201);
    }

    #[OA\Put(
        path: "/api/v1/admin/categories/{id}",
        summary: "Update a category (Admin)",
        tags: ["Admin Categories"],
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
            new OA\Response(response: 200, description: "Category updated successfully"),
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|unique:categories,slug,' . $category->id
        ]);
        $category->update($request->all());
        return $this->sendResponse(new CategoryResource($category), 'Catégorie mise à jour.');
    }

    #[OA\Delete(
        path: "/api/v1/admin/categories/{id}",
        summary: "Delete a category (Admin)",
        tags: ["Admin Categories"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Category deleted successfully"),
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return $this->sendResponse([], 'Catégorie supprimée.');
    }
}
