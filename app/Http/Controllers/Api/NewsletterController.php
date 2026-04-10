<?php

namespace App\Http\Controllers\Api;

use App\Models\NewsletterSubscriber;
use App\Http\Resources\SubscriberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class NewsletterController extends BaseController
{
    /**
     * Display a listing of subscribers (Admin).
     */
    #[OA\Get(
        path: "/api/v1/admin/newsletter/subscribers",
        summary: "List all newsletter subscribers (Admin)",
        tags: ["Admin Newsletter"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/NewsletterSubscriber")),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $subscribers = NewsletterSubscriber::latest('subscribed_at')->get();
        return $this->sendResponse(SubscriberResource::collection($subscribers), 'Liste des abonnés récupérée.');
    }

    /**
     * Handle a new subscription.
     */
    #[OA\Post(
        path: "/api/v1/newsletter/subscribe",
        summary: "Subscribe to the newsletter",
        tags: ["Newsletter"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Subscribed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email',
        ], [
            'email.unique' => 'Cet email est déjà inscrit à la newsletter.',
        ]);

        NewsletterSubscriber::create([
            'email' => $request->email,
            'unsubscribe_token' => Str::random(32),
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        return $this->sendResponse([], 'Votre inscription à la newsletter a été enregistrée avec succès.');
    }

    /**
     * Handle unsubscription.
     */
    #[OA\Post(
        path: "/api/v1/newsletter/unsubscribe",
        summary: "Unsubscribe from the newsletter",
        tags: ["Newsletter"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "token"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "token", type: "string", example: "random_token_string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Unsubscribed successfully"),
            new OA\Response(response: 404, description: "Subscriber not found or invalid token")
        ]
    )]
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $subscriber = NewsletterSubscriber::where('email', '=', $request->email)
            // ->where('unsubscribe_token', '=', $request->token)
            ->first();

        if (!$subscriber) {
            return $this->sendError('Abonné introuvable ou lien de désinscription invalide.', [], 404);
        }

        $subscriber->update(['is_active' => false]);

        return $this->sendResponse([], 'Vous avez été désinscrit de la newsletter.');
    }
}
