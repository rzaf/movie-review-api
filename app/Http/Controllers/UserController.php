<?php

namespace App\Http\Controllers;

use App\Http\Requests\users\DestroyUser;
use App\Http\Requests\users\UpdateUser;
use App\Http\Resources\UserResource;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class UserController extends Controller
{

    /**
     * login account to get new token.
     *
     * @OA\Post(
     *      path="/api/login",
     *      tags={"login"},
     *      @OA\RequestBody(
     *          description="request body",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="username",
     *                      description="username",
     *                      default="test"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="password",
     *                      default="1234"
     *                  ),
     *                  @OA\Property(
     *                      property="password_confirmation",
     *                      description="password_confirmation",
     *                      default="1234"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="ok",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="invalid username or password",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function login(Request $req)
    {
        $validated = $req->validate([
            'username' => 'required|string',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required|string',
        ]);
        $ok = Auth::attempt($validated);
        abort_if(!$ok, 401, "invalid username or password");
        $user = Auth::user();
        DB::table('personal_access_tokens')->where(['tokenable_id' => $user->id])->delete();
        return response([
            'new token' => $user->createToken('access token')->plainTextToken,
            'user' => new UserResource($user),
        ], 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $perpage = intval($req->query('perpage', 10));
        return UserResource::collection(User::paginate($perpage));
    }

    /**
     * create new user.
     *
     * @OA\Post(
     *      path="/api/users",
     *      tags={"user"},
     *      @OA\RequestBody(
     *          description="request body",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="name",
     *                      description="name"
     *                  ),
     *                  @OA\Property(
     *                      property="username",
     *                      description="username"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="email"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="password"
     *                  ),
     *                  @OA\Property(
     *                      property="password_confirmation",
     *                      description="password_confirmation"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="ok",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="invalid username or password",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function store(Request $req)
    {
        $validated = $req->validate([
            'name' => 'required|string',
            'username' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required|string',
        ]);
        try {
            $user = User::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            $column = explode('.', $e->errorInfo[2])[1];
            abort(400, "duplicate $column");
        }

        return response([
            'message' => 'user created',
            'date' => $user,
            'token' => $user->createToken('access token')->plainTextToken,
        ], 201);
    }


    /**
     * get specified user.
     *
     * @OA\Get(
     *      path="/api/users/{username}",
     *      tags={"user"},
     *      @OA\Parameter(
     *          name="username",
     *          in="path",
     *          description="name of username",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="user not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="user found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function show(string $username)
    {
        $user = User::where(['username' => $username])->first();
        abort_if($user == null, 404, 'user not found');
        return new UserResource($user);
    }

    
    /**
     * update specified user.
     *
     * @OA\Put(
     *      path="/api/users/{username}",
     *      tags={"user"},
     *      @OA\Parameter(
     *          name="username",
     *          in="path",
     *          description="name of user",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          description="Input data format",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="new_name",
     *                      description="new name",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="new_username",
     *                      description="new username"
     *                  ),
     *                  @OA\Property(
     *                      property="new_email",
     *                      description="new email"
     *                  ),
     *                  @OA\Property(
     *                      property="new_password",
     *                      description="new password"
     *                  ),
     *                  @OA\Property(
     *                      property="new_password_confirmation",
     *                      description="new password_confirmation"
     *                  )
     *              )
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="user updated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="user not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function update(UpdateUser $req)
    {
        $username = $req->route('username');
        $validated = $req->validated();
        try {
            $ok = User::where(['username' => $username])->update($validated);
        } catch (UniqueConstraintViolationException $e) {
            $column = explode('.', $e->errorInfo[2])[1];
            abort(400, "duplicate $column");
        }
        abort_if(!$ok, 404, 'user not found');
        return response([
            'message' => 'user updated',
        ], 200);
    }

    /**
     * update specified user.
     *
     * @OA\Delete(
     *      path="/api/users/{username}",
     *      tags={"user"},
     *      @OA\Parameter(
     *          name="username",
     *          in="path",
     *          description="name of user",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="string"
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="user updated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="user not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function destroy(DestroyUser $req, string $username)
    {
        $ok = User::where(['username' => $username])->delete();
        abort_if(!$ok, 404, 'user not found');
        return response([
            'message' => 'user deleted',
        ], 200);
    }
}
