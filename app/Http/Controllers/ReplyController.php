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
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class ReplyController extends Controller
{

    public function repliesOfReview(Request $req, $reviewId)
    {
        $perpage = intval($req->query('perpage', 10));
        $replies = Reply
            ::where(['review_id' => $reviewId])
            ->with(['user'])
            ->withCount('replies')
            ->withCount('likes')
            ->withCount('dislikes')
            ->paginate($perpage);
        return ReplyResource::collection($replies);
    }

    public function repliesOfReply(Request $req, $replyId)
    {
        $perpage = intval($req->query('perpage', 10));
        $replies = Reply
            ::where(['reply_id' => $replyId])
            ->with(['user'])
            ->withCount('replies')
            ->withCount('likes')
            ->withCount('dislikes')
            ->paginate($perpage);
        return ReplyResource::collection($replies);
    }

    public function show(Request $req, $id)
    {
        $reply = Reply
            ::where(['id' => $id])
            ->with([
                'user',
                'review' => ['user', 'movie'],
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
     * Store a newly created resource in storage.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
