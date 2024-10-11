<?php

namespace App\Http\Controllers;

use App\Http\Requests\movies\AddPerson;
use App\Http\Requests\movies\AddTag;
use App\Http\Requests\movies\DestroyMovie;
use App\Http\Requests\movies\DestroyMovieLike;
use App\Http\Requests\movies\RemoveTag;
use App\Http\Requests\movies\StoreMovie;
use App\Http\Requests\movies\StoreMovieLike;
use App\Http\Requests\movies\UpdateMovie;
use App\Http\Resources\MovieResource;
use App\Models\Like;
use App\Models\Movie;
use App\Models\MovieStaff;
use App\Models\MovieTag;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $perpage = intval($req->query('perpage', 10));
        return MovieResource::collection(Movie
            ::withAvg('reviews', 'score')
            ->withCount('likes')
            ->withCount('dislikes')
            ->withCount('reviews')
            ->paginate($perpage));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMovie $req)
    {
        $validated = $req->validated();
        try {
            $movie = Movie::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'duplicate url');
        }

        return response([
            'message' => 'movie created',
            'data' => new MovieResource($movie),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $url)
    {
        $movie = Movie::where(['url' => $url])
            ->with(['category:id,name', 'staff:id,name'])
            ->withAggregate('tags', 'name', 'group_concat')
            ->withAvg('reviews', 'score')
            ->withCount('reviews')
            ->withCount('likes')
            ->withCount('dislikes')
            ->first();
        abort_if($movie == null, 404, 'movie not found');
        return new MovieResource($movie);
        // return $movie;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMovie $req)
    {
        $validated = $req->validated();
        try {
            $ok = Movie::where(['url' => $req->route('url')])->update($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'duplicate url');
        }
        abort_if(!$ok, 404, sprintf("movie with url:`%s` not found", $req->route('url')));
        return response([
            'message' => 'movie edited',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyMovie $req, string $url)
    {
        $ok = Movie::where(['url' => $url])->delete();
        abort_if($ok == null, 404, 'movie not found');
        return response([
            'message' => 'movie deleted'
        ], 200);
    }

    public function storeLike(StoreMovieLike $req)
    {
        $validated = $req->validated();
        try {
            Like::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'movie already liked/disliked');
        }
        return response([
            'message' => 'like/dislike created',
        ], 201);
    }

    public function destroyLike(DestroyMovieLike $req)
    {
        $validated = $req->validated();
        $ok = Like::where($validated)->delete();
        abort_if(!$ok, 400, 'movie is not liked/disliked');
        return response([
            'message' => 'like/dislike deleted',
        ], 200);
    }

    public function addPerson(AddPerson $req)
    {
        $validated = $req->validated();
        try {
            MovieStaff::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            $job = $validated['job'];
            abort(400, "person already added to movie as $job");
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                abort(404, 'person id not found');
            }
        }
        return response([
            'message' => 'person added to movie',
        ], 201);
    }

    public function removePerson(AddPerson $req)
    {
        $validated = $req->validated();
        try {
            $ok = MovieStaff::where($validated)->delete();
            $job = $validated['job'];
            abort_if(!$ok, 400, "person was not in movie as $job");
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                abort(404, 'person id not found');
            }
        }
        return response([
            'message' => "person removed from movie as $job",
        ], 200);
    }

    public function addTag(AddTag $req)
    {
        $validated = $req->validated();
        try {
            MovieTag::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, "tag already added to movie");
        }
        return response([
            'message' => 'tag added to movie',
        ], 201);
    }

    public function removeTag(RemoveTag $req)
    {
        $validated = $req->validated();
        $ok = MovieTag::where($validated)->delete();
        abort_if(!$ok, 404, 'tag not in movie');

        return response([
            'message' => 'tag removed from movie',
        ], 200);
    }

}
