<?php

namespace App\Http\Controllers;

use App\Http\Requests\people\DestroyPerson;
use App\Http\Requests\people\StorePerson;
use App\Http\Requests\people\UpdatePerson;
use App\Http\Resources\PersonResource;
use App\Models\Following;
use App\Models\Person;
// use Illuminate\Database\UniqueConstraintViolationException;
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
            ::withCount('followers')
            ->withCount('movies')
            ->sortBy($req->query('sort'))
            ->paginate($perpage));
    }

    /**
     * search people.
     *
     * @OA\Get(
     *      path="/api/people/search/{search_term}",
     *      tags={"person"},
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
    public function search(Request $req, string $term)
    {
        $perpage = intval($req->query('perpage', 10));
        return PersonResource::collection(Person
            ::withCount('followers')
            ->withCount('movies')
            ->whereLike('name', "%$term%")
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
     *                      property="is_male",
     *                      description="gender of person",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="birth_date",
     *                      description="birth_date of person",
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
        $person = Person::create($validated);
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
    public function personMovies(string $id)
    {
        $person = Person
            ::with(['moviesWorkedIn'])
            ->withCount('followers')
            // ->withCount('movies')
            ->find($id);
        abort_if($person == null, 404, 'preson not found');
        // return $person;
        return new PersonResource($person);
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
     *                      property="birth_date",
     *                      description="new birth_date of person",
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
        $ok = Person::where(['id' => $req->route('id')])->update($validated);
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
