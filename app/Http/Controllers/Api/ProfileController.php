<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProfileController extends BaseController
{
    /**
     * Get the authenticated user's profile.
     */
    #[OA\Get(
        path: "/api/v1/profile",
        summary: "Get current user profile",
        tags: ["Profile"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/User")
            )
        ]
    )]
    public function show(Request $request): JsonResponse
    {
        return $this->sendResponse(new UserResource($request->user()), 'Profil récupéré.');
    }

    /**
     * Update the authenticated user's profile.
     */
    #[OA\Put(
        path: "/api/v1/profile",
        summary: "Update current user profile",
        tags: ["Profile"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe"),
                    new OA\Property(property: "bio", type: "string", example: "Author bio..."),
                    new OA\Property(property: "avatar_id", type: "string", format: "uuid", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Profile updated successfully")
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'avatar_id' => 'nullable|uuid|exists:media,id',
        ]);

        $user->update($request->only(['name', 'bio', 'avatar_id']));

        return $this->sendResponse(new UserResource($user), 'Profil mis à jour avec succès.');
    }

    /**
     * Delete the authenticated user's account.
     */
    #[OA\Delete(
        path: "/api/v1/profile",
        summary: "Delete current user account",
        tags: ["Profile"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Account deleted successfully")
        ]
    )]
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete user account
        $user->delete();

        return $this->sendResponse([], 'Compte supprimé avec succès.');
    }
}
