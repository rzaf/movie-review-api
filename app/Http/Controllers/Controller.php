<?php

namespace App\Http\Controllers;


/**
 *  @OA\OpenApi(
 *      @OA\Info(
 *          title="movie-reviews-api",
 *          version="1.0.0",
 *          description="API documentation movie-reviews-api"
 *      ),
 *      @OA\PathItem(
 *          path="/"
 *      ),
 *      @OA\Tag(
 *         name="category",
 *         description="categories",
 *      )
 * )
 * @OA\SecurityScheme(
 *      type="apiKey",
 *      securityScheme="bearer",
 *      in="header",
 *      name="Authorization"
 * )
 */


abstract class Controller
{

}
