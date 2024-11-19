<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/users', [UserController::class, 'index']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{username}', [UserController::class, 'show']);
Route::put('/users/{username}', [UserController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/users/{username}', [UserController::class, 'destroy'])->middleware('auth:sanctum');


// Route::apiResource('/categories', CategoryController::class);
Route::post('/categories', [CategoryController::class, 'store'])->middleware('auth:sanctum');
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{name}', [CategoryController::class, 'show']);
Route::put('/categories/{name}', [CategoryController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/categories/{name}', [CategoryController::class, 'destroy'])->middleware('auth:sanctum');

// Route::apiResource('/people', PersonController::class);
Route::post('/people', [PersonController::class, 'store'])->middleware('auth:sanctum');
Route::get('/people', [PersonController::class, 'index']);
Route::get('/people/{url}/medias', [PersonController::class, 'personMedias']);
Route::get('/people/{url}', [PersonController::class, 'show']);
Route::put('/people/{url}', [PersonController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/people/{url}', [PersonController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('/people/{url}/following', [PersonController::class, 'storeFollowing'])->middleware('auth:sanctum');
Route::delete('/people/{url}/following', [PersonController::class, 'destroyFollowing'])->middleware('auth:sanctum');

//// medias
Route::post('/medias', [MediaController::class, 'store'])->middleware('auth:sanctum');
Route::get('/medias', [MediaController::class, 'index']);
Route::get('/medias/{url}', [MediaController::class, 'show']);
Route::put('/medias/{url}', [MediaController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/medias/{url}', [MediaController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('/medias/{media_url}/people/{person_url}', [MediaController::class, 'addPerson'])->middleware('auth:sanctum');
Route::delete('/medias/{media_url}/people/{person_url}', [MediaController::class, 'removePerson'])->middleware('auth:sanctum');

Route::post('/medias/{media_url}/genres/{name}', [MediaController::class, 'addGenre'])->middleware('auth:sanctum');
Route::delete('/medias/{media_url}/genres/{name}', [MediaController::class, 'removeGenre'])->middleware('auth:sanctum');

Route::post('/medias/{media_url}/keywords/{name}', [MediaController::class, 'addKeyword'])->middleware('auth:sanctum');
Route::delete('/medias/{media_url}/keywords/{name}', [MediaController::class, 'removeKeyword'])->middleware('auth:sanctum');

Route::post('/medias/{media_url}/companies/{name}', [MediaController::class, 'addCompany'])->middleware('auth:sanctum');
Route::delete('/medias/{media_url}/companies/{name}', [MediaController::class, 'removeCompany'])->middleware('auth:sanctum');

Route::post('/medias/{media_url}/countries/{name}', [MediaController::class, 'addCountry'])->middleware('auth:sanctum');
Route::delete('/medias/{media_url}/countries/{name}', [MediaController::class, 'removeCountry'])->middleware('auth:sanctum');

Route::post('/medias/{media_url}/languages/{name}', [MediaController::class, 'addLanguage'])->middleware('auth:sanctum');
Route::delete('/medias/{media_url}/languages/{name}', [MediaController::class, 'removeLanguage'])->middleware('auth:sanctum');

Route::post('/medias/{media_url}/like', [MediaController::class, 'storeLike'])->middleware('auth:sanctum');
Route::delete('/medias/{media_url}/like', [MediaController::class, 'destroyLike'])->middleware('auth:sanctum');

//// reviews
Route::get('/reviews/{review_id}', [ReviewController::class, 'show']);
Route::delete('/reviews/{review_id}', [ReviewController::class, 'destroy'])->middleware('auth:sanctum');
Route::put('/reviews/{review_id}', [ReviewController::class, 'update'])->middleware('auth:sanctum');
Route::get('/medias/{media_url}/reviews', [ReviewController::class, 'index']);
Route::post('/medias/{media_url}/reviews', [ReviewController::class, 'store'])->middleware('auth:sanctum');

Route::post('/reviews/{review_id}/like', [ReviewController::class, 'storeLike'])->middleware('auth:sanctum');
Route::delete('/reviews/{review_id}/like', [ReviewController::class, 'destroyLike'])->middleware('auth:sanctum');

/// replies
Route::post('/replies', [ReplyController::class, 'store'])->middleware('auth:sanctum');
Route::put('/replies/{reply_id}', [ReplyController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/replies/{reply_id}', [ReplyController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/replies/{reply_id}', [ReplyController::class, 'show']);
Route::get('/reviews/{review_id}/replies', [ReplyController::class, 'repliesOfReview']);
Route::get('/replies/{reply_id}/replies', [ReplyController::class, 'repliesOfReply']);


Route::post('/replies/{reply_id}/like', [ReplyController::class, 'storeLike'])->middleware('auth:sanctum');
Route::delete('/replies/{reply_id}/like', [ReplyController::class, 'destroyLike'])->middleware('auth:sanctum');