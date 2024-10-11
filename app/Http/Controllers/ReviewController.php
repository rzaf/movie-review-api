<?php

namespace App\Http\Controllers;

use App\Http\Requests\reviews\DestroyReview;
use App\Http\Requests\reviews\DestroyReviewLike;
use App\Http\Requests\reviews\StoreReview;
use App\Http\Requests\reviews\StoreReviewLike;
use App\Http\Requests\reviews\UpdateReview;
use App\Http\Resources\ReviewResource;
use App\Models\Like;
use App\Models\Movie;
use App\Models\Review;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req, string $url)
    {
        $movie = Movie::where(['url' => $url])->first('id');
        abort_if($movie == null, 404, 'movie not found');
        $perpage = intval($req->query('perpage', 10));
        return ReviewResource::collection(Review
            ::where(['movie_id' => $movie->id])
            ->with(['user'])
            ->withCount('replies')
            ->withCount('likes')
            ->withCount('dislikes')
            ->paginate($perpage));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReview $req)
    {
        $vaildated = $req->validated();
        try {
            $review = Review::create($vaildated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'movie is already reviewd by user');
        }
        return response([
            'message' => 'review created',
            'data' => new ReviewResource($review),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $req, string $id)
    {
        $review = Review
            ::where(['id' => $id])
            ->with(['user', 'movie.category'])
            ->withCount('replies')
            ->withCount('likes')
            ->withCount('dislikes')
            ->first();
        abort_if($review == null, 404, 'review not found');
        return new ReviewResource($review);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReview $req)
    {
        $vaildated = $req->validated();
        $where = ['id' => $req->route('review_id')];
        $user = auth()->user();
        if ($user->role == 'normal') {
            $where['user_id'] = $user->id;
        }
        // $where['user_id'] = $user->id;
        $ok = Review::where($where)->update($vaildated);
        abort_if(!$ok, 400, 'review not found or not created by user');
        return response([
            'message' => 'review updated',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyReview $req)
    {
        $vaildated = $req->validated();
        $ok = Review::where($vaildated)->delete();
        abort_if(!$ok, 400, 'review not found or not created by user');
        return response([
            'message' => 'review deleted',
        ], 200);
    }

    public function storeLike(StoreReviewLike $req)
    {
        $validated = $req->validated();
        try {
            Like::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'review already liked/disliked');
        } 
        return response([
            'message' => 'like/dislike created',
        ], 201);
    }

    public function destroyLike(DestroyReviewLike $req)
    {
        $validated = $req->validated();
        $ok = Like::where($validated)->delete();
        abort_if(!$ok, 400, 'review is not liked/disliked');
        return response([
            'message' => 'like/dislike deleted',
        ], 200);
    }

}
