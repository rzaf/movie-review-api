<?php

namespace App\Http\Controllers;

use App\Http\Requests\reviews\DestroyReview;
use App\Http\Requests\reviews\DestroyReviewLike;
use App\Http\Requests\reviews\StoreReview;
use App\Http\Requests\reviews\StoreReviewLike;
use App\Http\Requests\reviews\UpdateReview;
use App\Http\Resources\ReviewResource;
use App\Models\Like;
use App\Models\Media;
use App\Models\Review;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * get reviews of specefied media.
     *
     * @OA\Get(
     *      path="/api/medias/{media_url}/reviews",
     *      tags={"review"},
     *      @OA\Parameter(
     *          name="media_url",
     *          in="path",
     *          description="url of media",
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="search_term",
     *          in="query",
     *          description="search_term",
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="username",
     *          in="query",
     *          description="username",
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="score",
     *          in="query",
     *          description="filter score",
     *          @OA\Schema(
     *              format="float",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="likes_count",
     *          in="query",
     *          description="filter likes_count",
     *          @OA\Schema(
     *              format="int64",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="dislikes_count",
     *          in="query",
     *          description="filter dislikes_count",
     *          @OA\Schema(
     *              format="int64",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="replies_count",
     *          in="query",
     *          description="filter replies_count",
     *          @OA\Schema(
     *              format="int64",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="number of page",
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="perpage",
     *          in="query",
     *          description="number of items in a page",
     *          @OA\Schema(
     *              format="int64",
     *              default=10
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort",
     *          in="query",
     *          description="sort by",
     *          @OA\Schema(
     *              format="string",
     *              enum={"newest","oldest","highest-score","lowest-score","most-likes","least-likes","most-dislikes","least-dislikes","most-replies","least-replies"},
     *              default=""
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="ok",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function index(Request $req, string $url)
    {
        $media = Media::where(['url' => $url])->first('id');
        abort_if($media == null, 404, 'media not found');
        $perpage = intval($req->query('perpage', 10));
        return ReviewResource::collection(Review
            ::filter($req->all())
            ->where(['media_id' => $media->id])
            ->with(['user'])
            ->withCount('replies')
            ->withCount('likes')
            ->withCount('dislikes')
            ->sortBy($req->query('sort'))
            ->paginate($perpage));
    }


    /**
     * create a review.
     *
     * @OA\Post(
     *      path="/api/medias/{media_url}/reviews",
     *      tags={"review"},
     *      @OA\Parameter(
     *          name="media_url",
     *          in="path",
     *          description="url of media",
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\RequestBody(
     *          description="request body",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="review",
     *                      description="review text",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="score",
     *                      description="integer between 0,100",
     *                      default=""
     *                  )
     *              )
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=201,
     *          description="review created",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="media is already reviewd ",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function store(StoreReview $req)
    {
        $vaildated = $req->validated();
        try {
            $review = Review::create($vaildated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'media is already reviewd by user');
        }
        return response([
            'message' => 'review created',
            'data' => new ReviewResource($review),
        ], 201);
    }

    /**
     * get specified review.
     *
     * @OA\Get(
     *      path="/api/reviews/{id}",
     *      tags={"review"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="id of review",
     *          required=true,
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="review not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="review found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function show(Request $req, string $id)
    {
        $review = Review
            ::where(['id' => $id])
            ->with(['user', 'media.category'])
            ->withCount('replies')
            ->withCount('likes')
            ->withCount('dislikes')
            ->first();
        abort_if($review == null, 404, 'review not found');
        return new ReviewResource($review);
    }


    /**
     * update specified review.
     *
     * @OA\Put(
     *      path="/api/reviews/{review_id}",
     *      tags={"review"},
     *      @OA\Parameter(
     *          name="review_id",
     *          in="path",
     *          description="id of review",
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\RequestBody(
     *          description="request body",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="review",
     *                      description="new review text",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="score",
     *                      description="new score: integer between 0,100",
     *                      default=""
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="review updated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="review not found or not created by user ",
     *          @OA\JsonContent()
     *      )
     * )
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
     * delete specified review.
     *
     * @OA\Delete(
     *      path="/api/reviews/{review_id}",
     *      tags={"review"},
     *      @OA\Parameter(
     *          name="review_id",
     *          in="path",
     *          description="id of review",
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="review deleted",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="review not found or not created by user ",
     *          @OA\JsonContent()
     *      )
     * )
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


    /**
     * like/dislike specified review.
     *
     * @OA\Post(
     *      path="/api/reviews/{review_id}/like",
     *      tags={"review"},
     *      @OA\Parameter(
     *          name="review_id",
     *          in="path",
     *          description="id of review",
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\RequestBody(
     *          description="request body",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="is_liked",
     *                      description="",
     *                      default=""
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="review liked/disliked",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="review already liked/disliked",
     *          @OA\JsonContent()
     *      )
     * )
     */
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

    /**
     * remove like/dislike from specified review.
     *
     * @OA\Delete(
     *      path="/api/reviews/{review_id}/like",
     *      tags={"review"},
     *      @OA\Parameter(
     *          name="review_id",
     *          in="path",
     *          description="id of review",
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="like/dislike deleted",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="review is not liked/disliked",
     *          @OA\JsonContent()
     *      )
     * )
     */
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
