<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Http\Resources\SettingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SettingsController extends BaseController
{
    /**
     * Display a listing of public settings.
     */
    #[OA\Get(
        path: "/api/v1/settings",
        summary: "List all public settings",
        tags: ["Settings"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Setting")),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $settings = Setting::all();
        return $this->sendResponse(SettingResource::collection($settings), 'Paramètres récupérés.');
    }

    /**
     * Update settings (Admin).
     */
    #[OA\Put(
        path: "/api/v1/admin/settings",
        summary: "Update site settings (Admin)",
        tags: ["Admin Settings"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                example: [
                    "site_name" => "Mamilo Blog",
                    "site_description" => "A modern blog platform"
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Settings updated successfully")
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $settings = $request->all();

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        return $this->sendResponse(SettingResource::collection(Setting::all()), 'Paramètres mis à jour.');
    }
}
