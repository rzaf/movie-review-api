<?php

namespace App\Http\Controllers;

use App\Http\Requests\replies\DestroyReply;
use App\Http\Requests\replies\DestroyReplyLike;
use App\Http\Requests\replies\StoreReply;
use App\Http\Requests\replies\StoreReplyLike;
use App\Http\Requests\replies\UpdateReply;
use App\Http\Resources\ReplyResource;
use App\Models\Like;
use App\Models\Reply;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class ReplyController extends Controller
{

    /**
     * get replies of specified review.
     *
     * @OA\Get(
     *      path="/api/reviews/{review_id}/replies",
     *      tags={"reply"},
     *      @OA\Parameter(
     *          name="review_id",
     *          in="path",
     *          description="id of review",
     *          @OA\Schema(
     *              format="int64",
     *              default=1
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
     *              enum={"newest","oldest","most-likes","least-likes","most-dislikes","least-dislikes","most-replies","least-replies"},
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
    public function repliesOfReview(Request $req, $reviewId)
    {
        $perpage = intval($req->query('perpage', 10));
        $replies = Reply
            ::filter($req->all())
            ->where(['review_id' => $reviewId])
            ->with(['user'])
            ->withCount('replies')
            ->withCount('likes')
            ->withCount('dislikes')
            ->sortBy($req->query('sort'))
            ->paginate($perpage);
        return ReplyResource::collection($replies);
    }


    /**
     * get replies of specified reply.
     *
     * @OA\Get(
     *      path="/api/replies/{reply_id}/replies",
     *      tags={"reply"},
     *      @OA\Parameter(
     *          name="reply_id",
     *          in="path",
     *          description="id of reply",
     *          @OA\Schema(
     *              format="int64",
     *              default=1
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
     *              enum={"newest","oldest","most-likes","least-likes","most-dislikes","least-dislikes","most-replies","least-replies"},
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
    public function repliesOfReply(Request $req, $replyId)
    {
        $perpage = intval($req->query('perpage', 10));
        $replies = Reply
            ::filter($req->all())
            ->where(['reply_id' => $replyId])
            ->with(['user'])
            ->withCount('replies')
            ->withCount('likes')
            ->withCount('dislikes')
            ->sortBy($req->query('sort'))
            ->paginate($perpage);
        return ReplyResource::collection($replies);
    }


    /**
     * get specified reply.
     *
     * @OA\Get(
     *      path="/api/replies/{id}",
     *      tags={"reply"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="id of reply",
     *          required=true,
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="reply not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="reply found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function show(Request $req, $id)
    {
        $reply = Reply
            ::where(['id' => $id])
            ->with([
                'user',
                'review' => ['user', 'media'],
                'reply' => ['user'],
            ])
            ->withCount('replies')
            ->withCount('likes')
            ->withCount('dislikes')
            ->first();
        abort_if($reply == null, 404, 'reply not found');
        return new ReplyResource($reply);

    }

    
    /**
     * create a reply.
     *
     * @OA\Post(
     *      path="/api/replies",
     *      tags={"reply"},
     *      @OA\RequestBody(
     *          description="request body",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="text",
     *                      description="reply text",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="review_id",
     *                      description="",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="reply_id",
     *                      description="",
     *                      default=""
     *                  ),
     *              )
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=201,
     *          description="reply created",
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
    public function store(StoreReply $req)
    {
        $vaildated = $req->validated();
        $reply = Reply::create($vaildated);
        
        return response([
            'message' => 'reply created',
            'data' => new ReplyResource($reply),
        ], 201);
    }

    
    /**
     * update specified review.
     *
     * @OA\Put(
     *      path="/api/replies/{reply_id}",
     *      tags={"reply"},
     *      @OA\Parameter(
     *          name="reply_id",
     *          in="path",
     *          description="id of reply",
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
     *      @OA\RequestBody(
     *          description="request body",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="text",
     *                      description="new reply text",
     *                      default=""
     *                  )
     *              )
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="reply updated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="reply not found or not created by user",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function update(UpdateReply $req, string $id)
    {
        $vaildated = $req->validated();
        $where = [
            'id' => $id
        ];
        $user = auth()->user();
        if ($user->role == 'normal') {
            $where['user_id'] = $user->id;
        }
        $ok = Reply::where($where)->update($vaildated);
        abort_if(!$ok, 404, 'reply not found or not created by user');
        return response([
            'message' => 'reply updated',
        ], 200);
    }

    /**
     * delete specified reply.
     *
     * @OA\Delete(
     *      path="/api/replies/{reply_id}",
     *      tags={"reply"},
     *      @OA\Parameter(
     *          name="reply_id",
     *          in="path",
     *          description="id of reply",
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
     *          description="reply deleted",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="reply not found or not created by user",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function destroy(DestroyReply $req, string $id)
    {
        $vaildated = $req->validated();
        $ok = Reply::where($vaildated)->delete();
        abort_if(!$ok, 404, 'reply not found or not created by user');
        return response([
            'message' => 'reply deleted',
        ], 200);
    }


    /**
     * like/dislike specified reply.
     *
     * @OA\Post(
     *      path="/api/replies/{reply_id}/like",
     *      tags={"reply"},
     *      @OA\Parameter(
     *          name="reply_id",
     *          in="path",
     *          description="id of reply",
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
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
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=201,
     *          description="reply liked/disliked",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="reply already liked/disliked",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function storeLike(StoreReplyLike $req)
    {
        $validated = $req->validated();
        try {
            Like::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'reply already liked/disliked');
        } 
        return response([
            'message' => 'like/dislike created',
        ], 201);
    }

    /**
     * remove like/dislike from specified reply.
     *
     * @OA\Delete(
     *      path="/api/replies/{reply_id}/like",
     *      tags={"reply"},
     *      @OA\Parameter(
     *          name="reply_id",
     *          in="path",
     *          description="id of reply",
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
     *          description="reply is not liked/disliked",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function destroyLike(DestroyReplyLike $req)
    {
        $validated = $req->validated();
        $ok = Like::where($validated)->delete();
        abort_if(!$ok, 400, 'reply is not liked/disliked');
        return response([
            'message' => 'like/dislike deleted',
        ], 200);
    }

}
