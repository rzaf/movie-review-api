<?php

namespace App\Http\Controllers;

use App\Http\Requests\medias\AddPerson;
use App\Http\Requests\medias\AddGenre;
use App\Http\Requests\medias\DestroyMedia;
use App\Http\Requests\medias\DestroyMediaLike;
use App\Http\Requests\medias\RemoveGenre;
use App\Http\Requests\medias\StoreMedia;
use App\Http\Requests\medias\StoreMediaLike;
use App\Http\Requests\medias\UpdateMedia;
use App\Http\Resources\MediaResource;
use App\Models\Like;
use App\Models\Media;
use App\Models\MediaStaff;
use App\Models\MediaGenre;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * get medias.
     *
     * @OA\Get(
     *      path="/api/medias",
     *      tags={"media"},
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
        return MediaResource::collection(Media
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
     * create new media.
     *
     * @OA\Post(
     *      path="/api/medias",
     *      tags={"media"},
     *      @OA\RequestBody(
     *          description="request body",
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="name",
     *                      description="name of media",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      description="url of media",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="release_date",
     *                      description="release_date of media",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="category_name",
     *                      description="category_name of media",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="summary",
     *                      description="summary of media",
     *                      default=""
     *                  ),
     *                  @OA\Property(
     *                      property="storyline",
     *                      description="storyline of media",
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
     *          description="media created",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function store(StoreMedia $req)
    {
        $validated = $req->validated();
        try {
            $media = Media::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'duplicate url');
        }

        return response([
            'message' => 'media created',
            'data' => new MediaResource($media),
        ], 201);
    }


    /**
     * get specified media.
     *
     * @OA\Get(
     *      path="/api/medias/{url}",
     *      tags={"media"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of media",
     *          required=true,
     *          @OA\Schema(
     *              format="string",
     *              default=""
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="media not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="media found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function show(string $url)
    {
        $media = Media::where(['url' => $url])
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
        abort_if($media == null, 404, 'media not found');
        return new MediaResource($media);
        // return $media;
    }

    /**
     * update specified category.
     *
     * @OA\Put(
     *      path="/api/medias/{url}",
     *      tags={"media"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of media",
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
     *                      description="new name of media",
     *                      type="string",
     *                      default="",
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      description="new url of media",
     *                      type="string",
     *                      default="",
     *                  ),
     *                  @OA\Property(
     *                      property="release_date",
     *                      description="new release_date of media",
     *                      type="string",
     *                      default="",
     *                  ),
     *                  @OA\Property(
     *                      property="category_name",
     *                      description="new category_name of media",
     *                      type="string",
     *                      default="",
     *                  ),
     *                  @OA\Property(
     *                      property="summary",
     *                      description="summary of media",
     *                      type="string",
     *                      default="",
     *                  ),
     *                  @OA\Property(
     *                      property="storyline",
     *                      description="storyline of media",
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
     *          description="media updated",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="media not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function update(UpdateMedia $req)
    {
        $validated = $req->validated();
        try {
            $ok = Media::where(['url' => $req->route('url')])->update($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'duplicate url');
        }
        abort_if(!$ok, 404, sprintf("media with url:`%s` not found", $req->route('url')));
        return response([
            'message' => 'media edited',
        ], 200);
    }

    /**
     * delete specified category.
     *
     * @OA\Delete(
     *      path="/api/medias/{url}",
     *      tags={"media"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of media",
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
     *          description="media deleted",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="media not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function destroy(DestroyMedia $req, string $url)
    {
        $ok = Media::where(['url' => $url])->delete();
        abort_if($ok == null, 404, 'media not found');
        return response([
            'message' => 'media deleted'
        ], 200);
    }


    /**
     * like/dislike specified media.
     *
     * @OA\Post(
     *      path="/api/medias/{url}/like",
     *      tags={"media"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of media",
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
     *                      description="liking or disliking media",
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
    public function storeLike(StoreMediaLike $req)
    {
        $validated = $req->validated();
        try {
            Like::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, 'media already liked/disliked');
        }
        return response([
            'message' => 'like/dislike created',
        ], 201);
    }


    /**
     * remove like/dislike of specified media.
     *
     * @OA\Delete(
     *      path="/api/medias/{url}/like",
     *      tags={"media"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of media",
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
    public function destroyLike(DestroyMediaLike $req)
    {
        $validated = $req->validated();
        $ok = Like::where($validated)->delete();
        abort_if(!$ok, 400, 'media is not liked/disliked');
        return response([
            'message' => 'like/dislike deleted',
        ], 200);
    }



    /**
     * add a person to specified media.
     *
     * @OA\Post(
     *      path="/api/medias/{url}/people/{person_id}",
     *      tags={"media"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of media",
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
     *                      description="job of person in media",
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
     *          description="person added to media",
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
            MediaStaff::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            $job = $validated['job'];
            abort(400, "person already added to media as $job");
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                abort(404, 'person id not found');
            }
        }
        return response([
            'message' => 'person added to media',
        ], 201);
    }


    /**
     * remove a person to specified media.
     *
     * @OA\Delete(
     *      path="/api/medias/{url}/people/{person_id}",
     *      tags={"media"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of media",
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
     *                      description="job of person in media",
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
     *          description="person removed from media",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="person not found",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="person was not in media as spceified job",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function removePerson(AddPerson $req)
    {
        $validated = $req->validated();
        try {
            $ok = MediaStaff::where($validated)->delete();
            $job = $validated['job'];
            abort_if(!$ok, 400, "person was not in media as $job");
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                abort(404, 'person id not found');
            }
        }
        return response([
            'message' => "person removed from media as $job",
        ], 200);
    }


    /**
     * add a genre to specified media.
     *
     * @OA\Post(
     *      path="/api/medias/{url}/genres/{name}",
     *      tags={"media"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of media",
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
     *          description="genre added to media",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="genre already added to media",
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
            MediaGenre::create($validated);
        } catch (UniqueConstraintViolationException $e) {
            abort(400, "genre already added to media");
        }
        return response([
            'message' => 'genre added to media',
        ], 201);
    }


    /**
     * remove genre from specified media.
     *
     * @OA\Delete(
     *      path="/api/medias/{url}/genres/{name}",
     *      tags={"media"},
     *      @OA\Parameter(
     *          name="url",
     *          in="path",
     *          description="url of media",
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
     *          description="genre removed from media",
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
        $ok = MediaGenre::where($validated)->delete();
        abort_if(!$ok, 404, 'genre not in media');

        return response([
            'message' => 'genre removed from media',
        ], 200);
    }

}
