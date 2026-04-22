<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class ContactController extends BaseController
{
    /**
     * Handle contact form submission.
     */
    #[OA\Post(
        path: "/api/v1/contact",
        summary: "Submit contact form",
        tags: ["Public"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "subject", "message"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                    new OA\Property(property: "subject", type: "string", example: "Inquiry about research"),
                    new OA\Property(property: "message", type: "string", example: "I would like to know more about your recent publication.")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Message submitted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Votre message a été envoyé avec succès.")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|min:3|max:255',
            'message' => 'required|string|min:10',
        ]);

        // In a real application, you might save this to a 'contacts' table or send an email.
        // For now, we will log it and return success to the user.
        Log::info('Contact Form Submission: ', $request->all());

        return $this->sendResponse([], 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
    }
}
