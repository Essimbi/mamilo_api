<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Event;
use App\Http\Resources\CommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CommentController extends BaseController
{
    /**
     * Display a listing of comments (Admin).
     */
    #[OA\Get(
        path: "/api/v1/admin/comments",
        summary: "List all comments (Admin)",
        tags: ["Admin Comments"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Comment")),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $comments = Comment::latest('created_at')->get();
        return $this->sendResponse(CommentResource::collection($comments), 'Liste des commentaires récupérée.');
    }

    /**
     * Store a newly created comment in storage.
     */
    #[OA\Post(
        path: "/api/v1/articles/{id}/comments",
        summary: "Submit a comment for an article",
        tags: ["Comments"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["author_name", "content"],
                properties: [
                    new OA\Property(property: "author_name", type: "string", example: "John Doe"),
                    new OA\Property(property: "author_avatar", type: "string", example: "https://example.com/avatar.png"),
                    new OA\Property(property: "content", type: "string", example: "Great article!")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Comment submitted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function store(Request $request, string $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        $request->validate([
            'author_name' => 'required|string|max:255',
            'author_avatar' => 'nullable|url',
            'content' => 'required|string',
        ]);

        $comment = $article->comments()->create([
            'author_name' => $request->author_name,
            'author_avatar' => $request->author_avatar,
            'content' => $request->content,
            'is_approved' => true,
        ]);

        \Illuminate\Support\Facades\Cache::forget('article_show_v3_' . $article->slug);

        return $this->sendResponse(new \App\Http\Resources\CommentResource($comment), 'Commentaire soumis avec succès.', [], 201);
    }

    /**
     * Approve a comment (Admin).
     */
    #[OA\Put(
        path: "/api/v1/admin/comments/{id}/approve",
        summary: "Approve a comment (Admin)",
        tags: ["Admin Comments"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Comment approved successfully")
        ]
    )]
    public function approve(string $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['is_approved' => true]);

        return $this->sendResponse(new CommentResource($comment), 'Commentaire approuvé.');
    }

    /**
     * Remove the specified comment from storage (Admin).
     */
    #[OA\Delete(
        path: "/api/v1/admin/comments/{id}",
        summary: "Delete a comment (Admin)",
        tags: ["Admin Comments"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Comment deleted successfully")
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return $this->sendResponse([], 'Commentaire supprimé.');
    }

    /**
     * Get comments for an article.
     */
    #[OA\Get(
        path: "/api/v1/articles/{id}/comments",
        summary: "Get comments for an article",
        tags: ["Comments"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Comment")),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function showByArticle(string $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $comments = $article->comments()->where('is_approved', true)->latest('created_at')->get();
        
        return $this->sendResponse(CommentResource::collection($comments), 'Commentaires récupérés.');
    }

    /**
     * Store a newly created comment for an event.
     */
    #[OA\Post(
        path: "/api/v1/events/{id}/comments",
        summary: "Submit a comment for an event",
        tags: ["Comments"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["author_name", "content"],
                properties: [
                    new OA\Property(property: "author_name", type: "string", example: "John Doe"),
                    new OA\Property(property: "author_avatar", type: "string", example: "https://example.com/avatar.png"),
                    new OA\Property(property: "content", type: "string", example: "Great event!")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Comment submitted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function storeEventComment(Request $request, string $id): JsonResponse
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'author_name' => 'required|string|max:255',
            'author_avatar' => 'nullable|url',
            'content' => 'required|string',
        ]);

        $comment = $event->comments()->create([
            'author_name' => $request->author_name,
            'author_avatar' => $request->author_avatar,
            'content' => $request->content,
            'is_approved' => false,
        ]);

        return $this->sendResponse([], 'Commentaire soumis avec succès. Il sera visible après modération.', [], 201);
    }

    /**
     * Get comments for an event.
     */
    #[OA\Get(
        path: "/api/v1/events/{id}/comments",
        summary: "Get comments for an event",
        tags: ["Comments"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Comment")),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function showByEvent(string $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $comments = $event->comments()->where('is_approved', true)->latest('created_at')->get();
        
        return $this->sendResponse(CommentResource::collection($comments), 'Commentaires récupérés.');
    }
}
