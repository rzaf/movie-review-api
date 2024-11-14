<?php

namespace App\Http\Controllers;

use App\Http\Requests\movies\AddPerson;
use App\Http\Requests\movies\AddGenre;
use App\Http\Requests\movies\DestroyMovie;
use App\Http\Requests\movies\DestroyMovieLike;
use App\Http\Requests\movies\RemoveGenre;
use App\Http\Requests\movies\StoreMovie;
use App\Http\Requests\movies\StoreMovieLike;
use App\Http\Requests\movies\UpdateMovie;
use App\Http\Resources\MovieResource;
use App\Models\Like;
use App\Models\Movie;
use App\Models\MovieStaff;
use App\Models\MovieGenre;
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
     *          name="search_term",
     *          in="query",
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
     *          name="reviews_count",
     *          in="query",
     *          description="filter reviews_count",
     *          @OA\Schema(
     *              format="int64",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="release_date",
     *          in="query",
     *          description="filter release_date",
     *          @OA\Schema(
     *              format="int64",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          description="filter category name",
     *          @OA\Schema(
     *              format="string",
     *              default=""
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
            ::select([
                'name',
                'url',
                'category_id',
                'release_date',
                'summary',
            ])
            ->filter($req->all())
            ->withAggregate('genres', 'name', 'group_concat')
            ->withAggregate('languages', 'name', 'group_concat')
            ->withAggregate('countries', 'country_name', 'group_concat')
            ->withAggregate('keywords', 'name', 'group_concat')
            ->withAggregate('companies', 'name', 'group_concat')
            ->withAvg('reviews', 'score')
            ->withCount('likes')
            ->withCount('dislikes')
            ->withCount('reviews')
            ->with('category:id,name')
            ->sortBy($req->query('sort'))
            ->paginate($perpage));
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
     *                      property="release_date",
     *                      description="release_date of movie",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="category_name",
     *                      description="category_name of movie",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="summary",
     *                      description="summary of movie",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="storyline",
     *                      description="storyline of movie",
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
            ->with(['category:id,name','staff:name,url'])
            ->withAggregate('genres', 'name', 'group_concat')
            ->withAggregate('languages', 'name', 'group_concat')
            ->withAggregate('countries', 'country_name', 'group_concat')
            ->withAggregate('keywords', 'name', 'group_concat')
            ->withAggregate('companies', 'name', 'group_concat')
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
     *                  required={"name"},
     *                  @OA\Property(
     *                      property="name",
     *                      description="new name of movie",
     *                      type="string",
     *                      default="",
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      description="new url of movie",
     *                      type="string",
     *                      default="",
     *                  ),
     *                  @OA\Property(
     *                      property="release_date",
     *                      description="new release_date of movie",
     *                      type="string",
     *                      default="",
     *                  ),
     *                  @OA\Property(
     *                      property="category_name",
     *                      description="new category_name of movie",
     *                      type="string",
     *                      default="",
     *                  ),
     *                  @OA\Property(
     *                      property="summary",
     *                      description="summary of movie",
     *                      type="string",
     *                      default="",
     *                  ),
     *                  @OA\Property(
     *                      property="storyline",
     *                      description="storyline of movie",
     *                      type="string",
     *                      default="",
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
     * add a genre to specified movie.
     *
     * @OA\Post(
     *      path="/api/movies/{url}/genres/{name}",
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
     *          description="name of genre",
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
     *          description="genre added to movie",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="genre already added to movie",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function addGenre(AddGenre $req)
    {
        $validated = $req->validated();
        try {
            MovieGenre::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, "genre already added to movie");
        }
        return response([
            'message' => 'genre added to movie',
        ], 201);
    }


    /**
     * remove genre from specified movie.
     *
     * @OA\Delete(
     *      path="/api/movies/{url}/genres/{name}",
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
     *          description="name of genre",
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
     *          description="genre removed from movie",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function removeGenre(RemoveGenre $req)
    {
        $validated = $req->validated();
        $ok = MovieGenre::where($validated)->delete();
        abort_if(!$ok, 404, 'genre not in movie');

        return response([
            'message' => 'genre removed from movie',
        ], 200);
    }

}
