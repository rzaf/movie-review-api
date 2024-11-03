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
     * get movies.
     *
     * @OA\Get(
     *      path="/api/movies",
     *      tags={"movie"},
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
     *              enum={"newest","oldest","newest-release","oldest-release","most-likes","least-likes","most-dislikes","least-dislikes","most-reviews","least-reviews","best-reviewed","worst-reviewed"},
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
    public function index(Request $req)
    {
        $perpage = intval($req->query('perpage', 10));
        return MovieResource::collection(Movie
            ::withAvg('reviews', 'score')
            ->withCount('likes')
            ->withCount('dislikes')
            ->withCount('reviews')
            ->sortBy($req->query('sort'))
            ->paginate($perpage));
    }

    /**
     * search movies.
     *
     * @OA\Get(
     *      path="/api/movies/search/{search_term}",
     *      tags={"movie"},
     *      @OA\Parameter(
     *          name="search_term",
     *          in="path",
     *          description="search_term",
     *          @OA\Schema(
     *              format="string",
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
     *              enum={"newest","oldest","newest-release","oldest-release","most-likes","least-likes","most-dislikes","least-dislikes","most-reviews","least-reviews","best-reviewed","worst-reviewed"},
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
    public function search(Request $req, string $term)
    {
        $perpage = intval($req->query('perpage', 10));
        return MovieResource::collection(Movie
            ::withAvg('reviews', 'score')
            ->withCount('likes')
            ->withCount('dislikes')
            ->withCount('reviews')
            ->whereLike('name', "%$term%")
            ->sortBy($req->query('sort'))
            ->paginate($perpage));;
    }

    /**
     * create new movie.
     *
     * @OA\Post(
     *      path="/api/movies",
     *      tags={"movie"},
     *      @OA\RequestBody(
     *          description="request body",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="name",
     *                      description="name of movie",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      description="url of movie",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="release_year",
     *                      description="release_year of movie",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="category_name",
     *                      description="category_name of movie",
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
     *          description="movie created",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
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
     * get specified movie.
     *
     * @OA\Get(
     *      path="/api/movies/{url}",
     *      tags={"movie"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of movie",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="movie not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="movie found",
     *          @OA\JsonContent()
     *      )
     * )
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
     * update specified category.
     *
     * @OA\Put(
     *      path="/api/movies/{url}",
     *      tags={"movie"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of movie",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\RequestBody(
     *          description="Input data format",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="name",
     *                      description="new name of movie",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      description="new url of movie",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="release_year",
     *                      description="new release_year of movie",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="category_name",
     *                      description="new category_name of movie",
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
     *          description="movie updated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="movie not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
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
     * delete specified category.
     *
     * @OA\Delete(
     *      path="/api/movies/{url}",
     *      tags={"movie"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of movie",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="movie deleted",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="movie not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function destroy(DestroyMovie $req, string $url)
    {
        $ok = Movie::where(['url' => $url])->delete();
        abort_if($ok == null, 404, 'movie not found');
        return response([
            'message' => 'movie deleted'
        ], 200);
    }


    /**
     * like/dislike specified movie.
     *
     * @OA\Post(
     *      path="/api/movies/{url}/like",
     *      tags={"movie"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of movie",
     *          required=true,
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
     *                      property="is_liked",
     *                      description="liking or disliking movie",
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
     *          description="like/dislike created",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
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


    /**
     * remove like/dislike of specified movie.
     *
     * @OA\Delete(
     *      path="/api/movies/{url}/like",
     *      tags={"movie"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of movie",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
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
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function destroyLike(DestroyMovieLike $req)
    {
        $validated = $req->validated();
        $ok = Like::where($validated)->delete();
        abort_if(!$ok, 400, 'movie is not liked/disliked');
        return response([
            'message' => 'like/dislike deleted',
        ], 200);
    }



    /**
     * add a person to specified movie.
     *
     * @OA\Post(
     *      path="/api/movies/{url}/people/{person_id}",
     *      tags={"movie"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of movie",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="person_id",
     *          in="path",
     *          description="id of person",
     *          required=true,
     *          @OA\Schema(
     *              format="int64",
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
     *                      property="job",
     *                      description="job of person in movie",
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
     *          description="person added to movie",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
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


    /**
     * remove a person to specified movie.
     *
     * @OA\Delete(
     *      path="/api/movies/{url}/people/{person_id}",
     *      tags={"movie"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of movie",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="person_id",
     *          in="path",
     *          description="id of person",
     *          required=true,
     *          @OA\Schema(
     *              format="int64",
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
     *                      property="job",
     *                      description="job of person in movie",
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
     *          description="person removed from movie",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="person not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="person was not in movie as spceified job",
     *          @OA\JsonContent()
     *      )
     * )
     */
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


    /**
     * add a tag to specified movie.
     *
     * @OA\Post(
     *      path="/api/movies/{url}/tags/{name}",
     *      tags={"movie"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of movie",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          in="path",
     *          description="name of tag",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=201,
     *          description="tag added to movie",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="tag already added to movie",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
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


    /**
     * remove tag from specified movie.
     *
     * @OA\Delete(
     *      path="/api/movies/{url}/tags/{name}",
     *      tags={"movie"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of movie",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          in="path",
     *          description="name of tag",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="tag removed from movie",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
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
