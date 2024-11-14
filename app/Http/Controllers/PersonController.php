<?php

namespace App\Http\Controllers;

use App\Http\Requests\people\DestroyPerson;
use App\Http\Requests\people\StorePerson;
use App\Http\Requests\people\UpdatePerson;
use App\Http\Resources\MovieStaffResource;
use App\Http\Resources\PersonResource;
use App\Models\Following;
use App\Models\MovieStaff;
use App\Models\Person;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    /**
     * get people.
     *
     * @OA\Get(
     *      path="/api/people",
     *      tags={"person"},
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
     *          name="followers_count",
     *          in="query",
     *          description="filter followers_count",
     *          @OA\Schema(
     *              format="int64",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="movies_count",
     *          in="query",
     *          description="filter movies_count",
     *          @OA\Schema(
     *              format="int64",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="gender",
     *          in="query",
     *          description="filter gender",
     *          @OA\Schema(
     *              format="string",
     *              enum={"male","female",""},
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort",
     *          in="query",
     *          description="sort by",
     *          @OA\Schema(
     *              format="string",
     *              enum={"newest-created","oldest-created","youngest","oldest","most-followers","least-followers","most-movies","least-movies"},
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
        return PersonResource::collection(Person
            ::select([
                'name',
                'url',
                'age',
                'is_male',
                'birth_date',
                'birth_country',
            ])
            ->filter($req->all())
            ->with('country:id,country_name')
            ->withCount('followers')
            ->withCount('movies')
            ->sortBy($req->query('sort'))
            ->paginate($perpage));
    }

    /**
     * create new person.
     *
     * @OA\Post(
     *      path="/api/people",
     *      tags={"person"},
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
     *                      property="name",
     *                      description="name of person",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      description="url of person",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="is_male",
     *                      description="gender of person",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="birth_date",
     *                      description="birth_date of person",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="about",
     *                      description="about person",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="country",
     *                      description="birth country of person",
     *                      default=""
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="person created",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function store(StorePerson $req)
    {
        $validated = $req->validated();
        try {
            $person = Person::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'duplicate url');
        }
        return response([
            'message' => 'preson created',
            'data' => new PersonResource($person),
        ], 201);
    }


    /**
     * get specified person.
     *
     * @OA\Get(
     *      path="/api/people/{id}",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="id of person",
     *          required=true,
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="person not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="person found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function show(string $id)
    {
        $person = Person
            ::withCount('followers')
            ->with('country:id,country_name')
            ->withCount('movies')
            ->find($id);
        abort_if($person == null, 404, 'preson not found');
        return new PersonResource($person);
    }

    /**
     * get movies of specified person.
     *
     * @OA\Get(
     *      path="/api/people/{id}/movies",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="id of person",
     *          required=true,
     *          @OA\Schema(
     *              format="int64",
     *              default=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="release_date",
     *          in="query",
     *          description="filter release_date of movies that person worked in",
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="work",
     *          in="query",
     *          description="filter job of person in movies",
     *          @OA\Schema(
     *              format="string",
     *              enum={"actor","producer","director","music","writer"},
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="search in movies that person worked",
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          description="filter category name of movies that person worked",
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
     *              enum={"newest","oldest","newest-release","oldest-release"},
     *              default=""
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="person not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="person found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function personMovies(Request $req, string $id)
    {
        $perpage = intval($req->query('perpage', 10));
        $person = Person
            ::withCount('movies')
            ->withCount('followers')
            ->find($id);
        abort_if($person == null, 404, 'preson not found');
        $movies = MovieStaff
            ::select([
                'movies.name',
                'movies.url',
                'movies.release_date',
                'movies.created_at',
                'categories.name as category_name',
                'movie_actors.job',
            ])
            ->join('movies', 'movie_id', '=', 'movies.id')
            ->join('categories', 'categories.id', '=', 'movies.category_id')
            ->whereRaw('person_id=?', $id)
            ->filter($req->all())
            ->sortBy($req->query('sort'))
            ->simplePaginate($perpage);
        return MovieStaffResource::collection($movies)->additional([
            'person' => new PersonResource($person),
        ]);
        // return ['person' => $personArray, 'movies' => MovieStaffResource::collection($movies)];
    }


    /**
     * update specified person.
     *
     * @OA\Put(
     *      path="/api/people/{id}",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="id of person",
     *          required=true,
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
     *                      property="name",
     *                      description="new name of person",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      description="url of person",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="birth_date",
     *                      description="new birth_date of person",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="about",
     *                      description="about person",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="country",
     *                      description="birth country of person",
     *                      default=""
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="person updated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="person not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function update(UpdatePerson $req)
    {
        $validated = $req->validated();
        try {
            $ok = Person::where(['id' => $req->route('id')])->update($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'duplicate url');
        }
        abort_if(!$ok, 404, sprintf('person:`%d` not found', $req->route('id')));
        return response([
            'message' => 'person edited',
        ], 200);
    }


    /**
     * delete specified person.
     *
     * @OA\Delete(
     *      path="/api/people/{id}",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="id of person",
     *          required=true,
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
     *          description="person deleted",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="person not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function destroy(DestroyPerson $req)
    {
        $ok = Person::where(['id' => $req->route('id')])->delete();
        abort_if(!$ok, 404, 'person not found');
        return response([
            'message' => 'person deleted successfully'
        ], 200);
    }



    /**
     * follow specified person.
     *
     * @OA\Post(
     *      path="/api/people/{id}/following",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="id of person",
     *          required=true,
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
     *          description="person followed",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="person already followed",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="person not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function storeFollowing(Request $req)
    {
        try {
            Following::create([
                'follower_id' => auth()->user()->id,
                'following_id' => $req->route('id'),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'person already followed');
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                abort(404, 'person id not found');
            }
        }
        return response([
            'message' => 'person followed',
        ], 200);
    }


    /**
     * follow specified person.
     *
     * @OA\Delete(
     *      path="/api/people/{id}/following",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="id of person",
     *          required=true,
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
     *          description="person unfollowed",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="person not found or not followed",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function destroyFollowing(Request $req)
    {
        $ok = Following::where([
            'follower_id' => auth()->user()->id,
            'following_id' => $req->route('id'),
        ])->delete();
        abort_if(!$ok, 400, 'person not found or not followed');
        return response([
            'message' => 'person unfollowed',
        ], 200);
    }
}
