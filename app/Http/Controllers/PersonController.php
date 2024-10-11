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
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $perpage = intval($req->query('perpage', 10));
        return PersonResource::collection(Person
            ::withCount('followers')
            ->paginate($perpage));
    }

    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $person = Person
            ::withCount('followers')
            ->withCount('movies')
            ->find($id);
        abort_if($person == null, 404, 'preson not found');
        return new PersonResource($person);
    }

    public function personMovies(int $id)
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyPerson $req)
    {
        $ok = Person::where(['id' => $req->route('id')])->delete();
        abort_if(!$ok, 404, 'person not found');
        return response([
            'message' => 'person deleted successfully'
        ], 200);
    }


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
