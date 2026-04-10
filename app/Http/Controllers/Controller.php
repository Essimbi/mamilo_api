<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "3CM API Blog",
    version: "1.0.0",
    description: "API documentation for the 3CM Blog project",
    contact: new OA\Contact(email: "admin@example.com")
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "API Server"
)]
abstract class Controller
{
    //
}
