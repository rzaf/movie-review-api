<?php

namespace App\Http\Controllers;

use App\Http\Requests\people\DestroyPerson;
use App\Http\Requests\people\StorePerson;
use App\Http\Requests\people\UpdatePerson;
use App\Http\Resources\MediaStaffResource;
use App\Http\Resources\PersonResource;
use App\Models\Following;
use App\Models\MediaStaff;
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
     *          name="medias_count",
     *          in="query",
     *          description="filter medias_count",
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
     *              enum={"newest-created","oldest-created","youngest","oldest","most-followers","least-followers","most-medias","least-medias"},
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
            ->withCount('medias')
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
     *      path="/api/people/{url}",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of person",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
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
    public function show(string $url)
    {
        $person = Person
            ::withCount('followers')
            ->with('country:id,country_name')
            ->withCount('medias')
            ->where('url', '=', $url)
            ->first();
        abort_if($person == null, 404, 'preson not found');
        return new PersonResource($person);
    }

    /**
     * get medias of specified person.
     *
     * @OA\Get(
     *      path="/api/people/{url}/medias",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of person",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="release_date",
     *          in="query",
     *          description="filter release_date of medias that person worked in",
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="work",
     *          in="query",
     *          description="filter job of person in medias",
     *          @OA\Schema(
     *              format="string",
     *              enum={"actor","producer","director","music","writer"},
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="search in medias that person worked",
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="category",
     *          in="query",
     *          description="filter category name of medias that person worked",
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
    public function personMedias(Request $req, string $url)
    {
        $perpage = intval($req->query('perpage', 10));
        $person = Person
            ::withCount('medias')
            ->withCount('followers')
            ->where('url', '=', $url)
            ->first();
        abort_if($person == null, 404, 'preson not found');
        $medias = MediaStaff
            ::select([
                'medias.name',
                'medias.url',
                'medias.release_date',
                'medias.created_at',
                'categories.name as category_name',
                'media_actors.job',
            ])
            ->join('medias', 'media_id', '=', 'medias.id')
            ->join('categories', 'categories.id', '=', 'medias.category_id')
            ->whereRaw('person_id=?', $person->id)
            ->filter($req->all())
            ->sortBy($req->query('sort'))
            ->simplePaginate($perpage);
        return MediaStaffResource::collection($medias)->additional([
            'person' => new PersonResource($person),
        ]);
        // return ['person' => $personArray, 'medias' => MediaStaffResource::collection($medias)];
    }


    /**
     * update specified person.
     *
     * @OA\Put(
     *      path="/api/people/{url}",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of person",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
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
    public function update(UpdatePerson $req, string $url)
    {
        $validated = $req->validated();
        try {
            $ok = Person::where('url', '=', $url)->update($validated);
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
     *      path="/api/people/{url}",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of person",
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
    public function destroy(string $url)
    {
        $ok = Person::where('url', '=', $url)->delete();
        abort_if(!$ok, 404, 'person not found');
        return response([
            'message' => 'person deleted successfully'
        ], 200);
    }



    /**
     * follow specified person.
     *
     * @OA\Post(
     *      path="/api/people/{url}/following",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of person",
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
    public function storeFollowing(string $url)
    {
        $person = Person::where('url', '=', $url)->first('id');
        abort_if($person == null, 404, 'preson not found');
        try {
            Following::create([
                'follower_id' => auth()->user()->id,
                'following_id' => $person->id,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'person already followed');
        }
        return response([
            'message' => 'person followed',
        ], 200);
    }


    /**
     * follow specified person.
     *
     * @OA\Delete(
     *      path="/api/people/{url}/following",
     *      tags={"person"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of person",
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
    public function destroyFollowing(string $url)
    {
        $person = Person::where('url', '=', $url)->first('id');
        abort_if($person == null, 404, 'preson not found');
        $ok = Following::where([
            'follower_id' => auth()->user()->id,
            'following_id' => $person->id,
        ])->delete();
        abort_if(!$ok, 400, 'person not followed');
        return response([
            'message' => 'person unfollowed',
        ], 200);
    }
}
