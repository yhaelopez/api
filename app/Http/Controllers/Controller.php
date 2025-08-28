<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="My API Project",
 *     description="My API documentation. Most services require API authentication. Use Bearer token for authorization.",
 *     termsOfService="Commercial",
 *
 *     @OA\Contact(
 *         email="support@myapp.com"
 *     )
 * )
 *
 * @OA\ExternalDocumentation(
 *     description="Find out more about YourApp!",
 *     url="https://github.com/yourapp"
 * )
 *
 * @OA\Components(
 *
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT"
 *     )
 * )
 */
abstract class Controller
{
    //
}
