<?php

namespace App\Http\Controllers;

use App\Http\Requests\categories\DestroyCategory;
use App\Http\Requests\categories\StoreCategory;
use App\Http\Requests\categories\UpdateCategory;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * get categories.
     *
     * @OA\Get(
     *      path="/api/categories",
     *      tags={"category"},
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
        return CategoryResource::collection(Category
            ::withCount('movies')
            ->paginate($perpage));
    }

    /**
     * search categories.
     *
     * @OA\Get(
     *      path="/api/categories/search/{search_term}",
     *      tags={"category"},
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
        return CategoryResource::collection(Category
            ::withCount('movies')
            ->whereLike('name', "%$term%")
            ->paginate($perpage));
    }

    /**
     * create new category.
     *
     * @OA\Post(
     *      path="/api/categories",
     *      tags={"category"},
     *      @OA\RequestBody(
     *          description="request body",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="name",
     *                      description="name of category",
     *                      default="new name"
     *                  )
     *              )
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=201,
     *          description="category created",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function store(StoreCategory $req)
    {
        $validated = $req->validated();
        try {
            $cat = Category::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'name' => 'name already exist'
            ]);
        }
        return response([
            'message' => 'category created',
            // 'data' => new CategoryResource($cat),
        ], 201);
    }


    /**
     * get specified category.
     *
     * @OA\Get(
     *      path="/api/categories/{name}",
     *      tags={"category"},
     *      @OA\Parameter(
     *          name="name",
     *          in="path",
     *          description="name of category",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="category not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="category found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function show(string $name)
    {
        $cat = Category::where(['name' => $name])->withCount('movies')->first();
        abort_if($cat == null, 404, 'cateogry not found');
        return new CategoryResource($cat);
    }


    /**
     * update specified category.
     *
     * @OA\Put(
     *      path="/api/categories/{name}",
     *      tags={"category"},
     *      @OA\Parameter(
     *          name="name",
     *          in="path",
     *          description="name of category",
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
     *                      description="new name of the category",
     *                      type="",
     *                  )
     *              )
     *          )
     *      ),
     *      security={
     *          {"bearer": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="category updated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="category not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function update(UpdateCategory $req)
    {
        $oldName = $req->route('name');
        $validated = $req->validated();
        try {
            $ok = Category::where(['name' => $oldName])->update($validated);
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'name' => 'category with name:`' . $validated['name'] . '` already exist'
            ]);
        }
        abort_if(!$ok, 404, sprintf("category:`%s` not found", $validated['name']));
        return response([
            'message' => 'category edited',
        ], 200);
    }

    /**
     * remove specified category.
     *
     * @OA\Delete(
     *      path="/api/categories/{name}",
     *      tags={"category"},
     *      @OA\Parameter(
     *          name="name",
     *          in="path",
     *          description="name of category",
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
     *          description="category deleted",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="category not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function destroy(DestroyCategory $req)
    {
        $ok = Category::where(['name' => $req->route('name')])->delete();
        abort_if($ok == null, 404, 'cateogry not found');
        return response([
            'message' => 'category deleted successfully'
        ], 200);
    }
}

