<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MovieController;
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
Route::get('/people/{id}/movies', [PersonController::class, 'personMovies']);
Route::get('/people/{id}', [PersonController::class, 'show']);
Route::put('/people/{id}', [PersonController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/people/{id}', [PersonController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('/people/{id}/following', [PersonController::class, 'storeFollowing'])->middleware('auth:sanctum');
Route::delete('/people/{id}/following', [PersonController::class, 'destroyFollowing'])->middleware('auth:sanctum');

//// movies
Route::post('/movies', [MovieController::class, 'store'])->middleware('auth:sanctum');
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{url}', [MovieController::class, 'show']);
Route::put('/movies/{url}', [MovieController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/movies/{url}', [MovieController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('/movies/{movie_url}/people/{person_id}', [MovieController::class, 'addPerson'])->middleware('auth:sanctum');
Route::delete('/movies/{movie_url}/people/{person_id}', [MovieController::class, 'removePerson'])->middleware('auth:sanctum');

Route::post('/movies/{movie_url}/genres/{name}', [MovieController::class, 'addGenre'])->middleware('auth:sanctum');
Route::delete('/movies/{movie_url}/genres/{name}', [MovieController::class, 'removeGenre'])->middleware('auth:sanctum');

Route::post('/movies/{movie_url}/like', [MovieController::class, 'storeLike'])->middleware('auth:sanctum');
Route::delete('/movies/{movie_url}/like', [MovieController::class, 'destroyLike'])->middleware('auth:sanctum');

//// reviews
Route::get('/reviews/{review_id}', [ReviewController::class, 'show']);
Route::delete('/reviews/{review_id}', [ReviewController::class, 'destroy'])->middleware('auth:sanctum');
Route::put('/reviews/{review_id}', [ReviewController::class, 'update'])->middleware('auth:sanctum');
Route::get('/movies/{movie_url}/reviews', [ReviewController::class, 'index']);
Route::post('/movies/{movie_url}/reviews', [ReviewController::class, 'store'])->middleware('auth:sanctum');

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