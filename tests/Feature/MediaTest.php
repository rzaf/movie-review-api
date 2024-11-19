<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Genre;
use App\Models\Keyword;
use App\Models\Language;
use App\Models\Like;
use App\Models\Media;
use App\Models\Country;
use App\Models\MediaGenre;
use App\Models\Person;
use App\Models\User;
use Database\Factories\MediaFactory;
use Database\Factories\UserFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\LanguagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new CategorySeeder)->run();
        (new CountrySeeder)->run();
        (new LanguagesSeeder)->run();
    }

    public function test_that_media_factory_works(): void
    {
        $this->assertDatabaseCount(Media::class, 0);
        $media = Media::factory()->create();
        $this->assertModelExists($media);
        $this->assertDatabaseCount(Media::class, 1);
    }

    public function test_that_media_show_method_gives_404_when_media_url_doesnt_exist(): void
    {
        $response = $this->json('GET', 'api/medias/test');
        $response->assertNotFound();
        $this->assertJson($response->content());
    }

    public function test_that_media_show_method_gives_correct_media(): void
    {
        $media = media::factory()->create();
        $response = $this->json('GET', "api/medias/$media->url");
        $this->assertJson($response->content());
        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $media->name,
            'summary' => $media->summary,
            'storyline' => $media->storyline,
            'release_date' => $media->release_date,
            'url' => $media->url,
            'likes_count' => 0,
            'dislikes_count' => 0,
            'reviews_count' => 0,
            'average_score' => null,
        ]);
    }

    public function test_that_media_show_method_gives_correct_likes_count(): void
    {
        $media = media::factory()->create();
        $this->assertEquals($media->likes_count, 0);
        UserFactory::times(5)->create()->each(function ($user) use ($media) {
            $media->likes()->create([
                'is_liked' => 1,
                'user_id' => $user->id,
            ]);
        });
        $response = $this->json('GET', "api/medias/$media->url");
        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $media->name,
            'url' => $media->url,
            'likes_count' => 5,
        ]);
    }

    public function test_that_media_show_method_gives_correct_dislikes_count(): void
    {
        $media = media::factory()->create();
        $this->assertEquals($media->likes_count, 0);
        UserFactory::times(5)->create()->each(function ($user) use ($media) {
            $media->likes()->create([
                'is_liked' => 0,
                'user_id' => $user->id,
            ]);
        });
        $response = $this->json('GET', "api/medias/$media->url");
        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $media->name,
            'url' => $media->url,
            'dislikes_count' => 5,
        ]);
    }

    public function test_that_media_show_method_gives_correct_reviews_count(): void
    {
        $media = media::factory()->create();
        $this->assertEquals($media->likes_count, 0);
        UserFactory::times(5)->create()->each(function ($user) use ($media) {
            $media->reviews()->create([
                'score' => 50,
                'user_id' => $user->id,
            ]);
        });
        $response = $this->json('GET', "api/medias/$media->url");
        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $media->name,
            'url' => $media->url,
            'reviews_count' => 5,
            'average_score' => "5.00",
        ]);
    }

    public function test_that_media_index_method_gives_empty_when_no_medias_exist(): void
    {
        $response = $this->json('GET', "api/medias")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_that_media_index_method_gives_correct_number_of_medias_with_perpage(): void
    {
        media::factory(4)->create();
        $this->json('GET', "api/medias")
            ->assertOk()
            ->assertJsonCount(4, 'data');

        media::factory(10)->create();
        $this->json('GET', "api/medias?perpage=8")
            ->assertOk()
            ->assertJsonCount(8, 'data');
    }

    public function test_that_media_index_method_filtering_works_correctly(): void
    {
        UserFactory::times(5)->create();
        $userIds = User::pluck('id');
        $medias = Media::factory(15)->create()->each(function (Media $media) use ($userIds) {
            foreach (fake()->randomElements($userIds, rand(1, 5)) as $id) {
                $media->likes()->create([
                    'user_id' => $id,
                    'is_liked' => fake()->boolean(rand(20, 80)),
                ]);
            }
            foreach (fake()->randomElements($userIds, rand(1, 5)) as $id) {
                $media->reviews()->create([
                    'user_id' => $id,
                    'reviews' => 'test',
                    'score' => rand(10, 100),
                ]);
            }
        });


        // (new CategorySeeder)->run();
        // MediaFactory::times(5)->create()->each(function (Media $media) use ($mediasIds) {
        //     $media->staff()->attach(fake()->randomElements($mediasIds, rand(1, 5)), ['job' => 'actor']);
        // });
        $this->json('GET', "api/medias?perpage=25&likes_count=0")
            ->assertOk()
            ->assertJsonCount(Media::has('likes', '=', 0)->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&likes_count=1")
            ->assertOk()
            ->assertJsonCount(Media::has('likes', '=', 1)->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&likes_count=2")
            ->assertOk()
            ->assertJsonCount(Media::has('likes', '=', 2)->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&dislikes_count=0")
            ->assertOk()
            ->assertJsonCount(Media::has('dislikes', '=', 0)->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&dislikes_count=1")
            ->assertOk()
            ->assertJsonCount(Media::has('dislikes', '=', 1)->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&dislikes_count=2")
            ->assertOk()
            ->assertJsonCount(Media::has('dislikes', '=', 2)->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&reviews_count=0")
            ->assertOk()
            ->assertJsonCount(Media::has('reviews', '=', 0)->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&reviews_count=1")
            ->assertOk()
            ->assertJsonCount(Media::has('reviews', '=', 1)->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&reviews_count=2")
            ->assertOk()
            ->assertJsonCount(Media::has('reviews', '=', 2)->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&search_term=a")
            ->assertOk()
            ->assertJsonCount(Media::whereLike('name', 'a')->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&search_term=b")
            ->assertOk()
            ->assertJsonCount(Media::whereLike('name', 'b')->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&search_term=1")
            ->assertOk()
            ->assertJsonCount(Media::whereLike('name', '1')->count(), 'data');

        $this->json('GET', "api/medias?perpage=25&search_term=1")
            ->assertOk()
            ->assertJsonCount(Media::whereLike('name', '1')->count(), 'data');
    }

    public function test_that_media_store_method_validates_inputs_correctly(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $this->json('POST', "api/medias", [])
            ->assertSee('The name field is required')
            ->assertSee('The url field is required')
            ->assertSee('The release date field is required')
            ->assertSee('The category name field is required')
            ->assertStatus(422)
            ->assertJsonCount(4, 'errors');

        $this->json('POST', "api/medias", [
            'name' => 'test',
        ])->assertDontSee('The name field is required')
            ->assertSee('The url field is required')
            ->assertSee('The release date field is required')
            ->assertSee('The category name field is required')
            ->assertStatus(422)
            ->assertJsonCount(3, 'errors');

        $this->json('POST', "api/medias", [
            'name' => 'test',
            'url' => 'test',
        ])->assertDontSee('The name field is required')
            ->assertDontSee('The url field is required')
            ->assertSee('The release date field is required')
            ->assertSee('The category name field is required')
            ->assertStatus(422)
            ->assertJsonCount(2, 'errors');

        $this->json('POST', "api/medias", [
            'name' => 'test',
            'url' => 'test',
            'category_name' => 'Movie',
        ])->assertDontSee('The name field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The category name field is required')
            ->assertSee('The release date field is required')
            ->assertStatus(422)
            ->assertJsonCount(1, 'errors');

        $this->json('POST', "api/medias", [
            'name' => 'test',
            'url' => 'test',
            'category_name' => 'Movie',
            'release_date' => 'invalid',
        ])->assertDontSee('The name field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The category name field is required')
            ->assertDontSee('The release date field is required')
            ->assertSee('The release date field must be a valid date.')
            ->assertStatus(422)
            ->assertJsonCount(1, 'errors');

        $this->json('POST', "api/medias", [
            'name' => 'test',
            'url' => 'test',
            'category_name' => 'Movie',
            'release_date' => '2000-100-1',
        ])->assertDontSee('The name field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The category name field is required')
            ->assertDontSee('The release date field is required')
            ->assertSee('The release date field must be a valid date.')
            ->assertStatus(422)
            ->assertJsonCount(1, 'errors');

        $this->json('POST', "api/medias", [
            'name' => 'test',
            'url' => 'test',
            'category_name' => 'Movie',
            'release_date' => '2000-1-1',
        ])->assertDontSee('The name field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The category name field is required')
            ->assertDontSee('The release date field is required')
            ->assertStatus(201);

        $this->json('POST', "api/medias", [
            'name' => 'test',
            'url' => 'test',
            'category_name' => 'not_existing',
            'release_date' => '2000-1-1',
        ])->assertSee('invalid category name')
            ->assertStatus(400);
    }

    public function test_that_media_store_method_creates_media_correctly(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $media = MediaFactory::times(1)->makeOne();
        $this->assertDatabaseMissing(Media::class, $media->toArray());
        $response = $this->json('POST', "api/medias", array_merge($media->toArray(), [
            'category_name' => 'Movie',
        ]));
        $response->assertCreated();
        $this->assertDatabaseCount(media::class, 1);
        $this->assertDatabaseHas(media::class, array_merge($media->toArray(), [
            'category_id' => 1,
        ]));
        $response->assertJsonFragment([
            'name' => $media->name,
            'summary' => $media->summary,
            'storyline' => $media->storyline,
            'url' => $media->url,
        ]);
    }

    public function test_that_media_store_method_gives_error_on_duplicate_url(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $media = MediaFactory::times(1)->createOne();
        $newMedia = MediaFactory::times(1)->makeOne();
        $newMedia->url = $media->url;
        $this->actingAs($adminUser);
        $this->json('POST', "api/medias", array_merge($newMedia->toArray(), [
            'category_name' => 'Movie',
        ]))->assertSee('duplicate url')
            ->assertStatus(400);
    }

    public function test_that_media_update_method_validates_inputs_correctly(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $this->json('PUT', "api/medias/test", [])
            ->assertSee('The name field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The category name field is required')
            ->assertDontSee('The release date field is required')
            ->assertStatus(422)
            ->assertJsonCount(1, 'errors');
    }

    public function test_that_media_update_method_gives_404__when_media_url_doesnt_exist(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $this->json('PUT', "api/medias/test", [
            'name' => 'test'
        ])->assertNotFound();
    }

    public function test_that_media_update_method_updates_media_correctly(): void
    {
        $media = Media::factory()->create();
        $newMedia = Media::factory()->makeOne();
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('PUT', "api/medias/$media->url", array_merge($newMedia->toArray(), [
            'category_name' => Category::find($newMedia->category_id)->name,
        ]))
            ->assertOk();
        $this->assertDatabaseMissing(Media::class, $media->toArray());
        $this->assertDatabaseHas(Media::class, $newMedia->toArray());
    }

    public function test_that_media_update_method_gives_error_on_duplicate_url(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin',]);
        $this->actingAs($adminUser);
        $oldMedia = MediaFactory::times(1)->createOne();
        $media = MediaFactory::times(1)->createOne();
        $this->json('PUT', "api/medias/$media->url", [
            'name' => 'test',
            'url' => $oldMedia->url,
        ])
            ->assertSee('duplicate url')
            ->assertStatus(400);
    }

    public function test_that_media_destroy_method_gives_404__when_media_url_doesnt_exist(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/test")
            ->assertNotFound();
    }

    public function test_that_media_destroy_method_deletes_existing_media_correctly(): void
    {
        $media = Media::factory()->create();
        $adminUser = User::factory()->create(['role' => 'admin',]);
        $this->actingAs($adminUser);
        $this->assertModelExists($media);
        $response = $this->json('DELETE', "api/medias/$media->url");
        $response->assertOk();
        $this->assertModelMissing($media);
    }

    /// storeLike tests
    public function test_that_media_storeLike_method_gives_validation_error(): void
    {
        $normalUser = User::factory()->create();
        $this->actingAs($normalUser);
        $this->json('POST', "api/medias/test/like")
            ->assertStatus(422)
            ->assertSee('The is liked field is required');
    }

    public function test_that_media_storeLike_method_gives_404_when_media_url_dosent_exist(): void
    {
        $normalUser = User::factory()->create();
        $this->actingAs($normalUser);
        $this->json('POST', "api/medias/test/like", [
            'is_liked' => 1,
        ])
            ->assertNotFound()
            ->assertSee('media not found');
    }

    public function test_that_media_storeLike_method_adds_like_correctly(): void
    {
        $media = Media::factory()->create();
        $normalUser = User::factory()->create();
        $this->actingAs($normalUser);
        $this->json('POST', "api/medias/$media->url/like", ['is_liked' => 1])
            ->assertCreated();
        $this->assertDatabaseHas(Like::class, [
            'is_liked' => 1,
            'likeable_type' => Media::class,
            'user_id' => $normalUser->id,
            'likeable_id' => $media->id,
        ]);
    }

    public function test_that_media_storeLike_method_adds_dislike_correctly(): void
    {
        $media = Media::factory()->create();
        $normalUser = User::factory()->create();
        $this->actingAs($normalUser);
        $this->json('POST', "api/medias/$media->url/like", ['is_liked' => 0])
            ->assertCreated();
        $this->assertDatabaseHas(Like::class, [
            'is_liked' => 0,
            'likeable_type' => Media::class,
            'user_id' => $normalUser->id,
            'likeable_id' => $media->id,
        ]);
    }

    public function test_that_media_storeLike_method_gives_error_when_media_is_already_liked(): void
    {
        $normalUser = User::factory()->create();
        $media = Media::factory()->create();
        $media->likes()->create([
            'user_id' => $normalUser->id,
            'is_liked' => 1,
        ]);
        $this->actingAs($normalUser);
        $this->json('POST', "api/medias/$media->url/like", ['is_liked' => 1])
            ->assertStatus(400)
            ->assertSee('already liked');
    }

    /// destroyLike tests
    public function test_that_media_destroyLike_method_gives_404_when_media_url_dosent_exist(): void
    {
        $normalUser = User::factory()->create();
        $this->actingAs($normalUser);
        $this->json('DELETE', "api/medias/test/like")
            ->assertNotFound()
            ->assertSee('media not found');
    }

    public function test_that_media_destroyLike_method_remove_like_correctly(): void
    {
        $normalUser = User::factory()->create();
        $media = Media::factory()->create();
        $media->likes()->create([
            'user_id' => $normalUser->id,
            'is_liked' => 1,
        ]);
        $this->actingAs($normalUser);
        $this->json('DELETE', "api/medias/$media->url/like")
            ->assertStatus(200);
        $this->assertDatabaseMissing(Like::class, [
            'likeable_type' => Media::class,
            'user_id' => $normalUser->id,
            'likeable_id' => $media->id,
        ]);
    }

    public function test_that_media_destroyLike_method_gives_error_when_media_not_liked(): void
    {
        $normalUser = User::factory()->create();
        $media = Media::factory()->create();
        $this->actingAs($normalUser);
        $this->json('DELETE', "api/medias/$media->url/like")
            ->assertStatus(400)
            ->assertSee('media is not liked/disliked');
    }


    ////////// addGenre tests

    public function test_that_media_addGenre_method_404_when_media_url_doesnt_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $this->json('POST', "api/medias/test/genres/test")
            ->assertStatus(404)
            ->assertSee('media not found');
    }

    public function test_that_media_addGenre_method_adds_genre_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $media = Media::factory()->create();
        $this->actingAs($adminUser);

        $genre = Genre::factory()->createOne();
        $this->json('POST', "api/medias/$media->url/genres/$genre->name")
            ->assertCreated();
        $this->assertDatabaseHas('media_genres', [
            'genre_id' => $genre->id,
            'media_id' => $media->id,
        ]);

        $this->json('POST', "api/medias/$media->url/genres/test")
            ->assertCreated();
        $this->assertDatabaseHas('genres', [
            'name' => 'test',
        ]);
        $this->assertDatabaseHas(MediaGenre::class, [
            'genre_id' => Genre::where('name', '=', 'test')->first()->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_that_media_addGenre_method_gives_error_when_genre_already_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $media = Media::factory()->create();
        $this->actingAs($adminUser);
        $genre = Genre::factory(1)->createOne();
        $media->genres()->attach($genre->id);
        $this->json('POST', "api/medias/$media->url/genres/$genre->name")
            ->assertStatus(400)
            ->assertSee('genre already added');
    }

    ////////// removeGenre tests

    public function test_that_media_removeGenre_method_gives_404_when_genre_name_dosent_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/test/genres/test")
            ->assertNotFound()
            ->assertSee('media not found');
        $media = Media::factory()->create();
        $this->json('DELETE', "api/medias/$media->url/genres/test")
            ->assertNotFound()
            ->assertSee('genre not found');
    }

    public function test_that_media_removeGenre_method_remove_genre_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();
        $genre = Genre::factory()->create();
        $media->genres()->attach($genre->id);
        $this->json('DELETE', "api/medias/$media->url/genres/$genre->name")
            ->assertStatus(200);
        $this->assertDatabaseMissing(MediaGenre::class, [
            'genre_id' => $genre->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_that_media_removeGenre_method_gives_error_when_genre_not_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $genre = Genre::factory()->create();
        $media = Media::factory()->create();
        $this->json('DELETE', "api/medias/$media->url/genres/$genre->name")
            ->assertStatus(400);
    }

    ////////// addKeyword tests

    public function test_that_media_addKeyword_method_404_when_media_url_doesnt_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $this->json('POST', "api/medias/test/keywords/test")
            ->assertStatus(404)
            ->assertSee('media not found');
    }

    public function test_that_media_addKeyword_method_adds_keyword_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();

        $keyword = Keyword::factory()->createOne();
        $this->json('POST', "api/medias/$media->url/keywords/$keyword->name")
            ->assertCreated();
        $this->assertDatabaseHas('media_keywords', [
            'keyword_id' => $keyword->id,
            'media_id' => $media->id,
        ]);

        $this->json('POST', "api/medias/$media->url/keywords/test")
            ->assertCreated();
        $this->assertDatabaseHas('keywords', [
            'name' => 'test',
        ]);
        $this->assertDatabaseHas('media_keywords', [
            'keyword_id' => keyword::where('name', '=', 'test')->first()->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_that_media_addKeyword_method_gives_error_when_keyword_already_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();
        $keyword = Keyword::factory()->create();
        $media->keywords()->attach($keyword->id);
        $this->json('POST', "api/medias/$media->url/keywords/$keyword->name")
            ->assertStatus(400)
            ->assertSee('keyword already added');
    }

    ////////// removeKeyword tests

    public function test_that_media_removeKeyword_method_gives_404_when_keyword_name_dosent_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/test/keywords/test")
            ->assertNotFound()
            ->assertSee('media not found');
        $media = Media::factory()->create();
        $this->json('DELETE', "api/medias/$media->url/keywords/test")
            ->assertNotFound()
            ->assertSee('keyword not found');
    }

    public function test_that_media_removeKeyword_method_remove_keyword_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();
        $keyword = Keyword::factory()->create();
        $media->keywords()->attach($keyword->id);
        $this->json('DELETE', "api/medias/$media->url/keywords/$keyword->name")
            ->assertStatus(200);
        $this->assertDatabaseMissing('media_keywords', [
            'keyword_id' => $keyword->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_that_media_removeKeyword_method_gives_error_when_keyword_not_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $keyword = Keyword::factory()->create();
        $media = Media::factory()->create();
        $this->json('DELETE', "api/medias/$media->url/keywords/$keyword->name")
            ->assertStatus(400)
            ->assertSee('keyword not in media');
    }


    ////////// addCompany tests

    public function test_that_media_addCompany_method_404_when_media_url_doesnt_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $this->json('POST', "api/medias/test/companies/test")
            ->assertStatus(404)
            ->assertSee('media not found');
    }

    public function test_that_media_addCompany_method_adds_company_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();

        $company = Company::factory()->createOne();
        $this->json('POST', "api/medias/$media->url/companies/$company->name")
            ->assertCreated();
        $this->assertDatabaseHas('media_companies', [
            'company_id' => $company->id,
            'media_id' => $media->id,
        ]);

        $this->json('POST', "api/medias/$media->url/companies/test")
            ->assertCreated();
        $this->assertDatabaseHas('companies', [
            'name' => 'test',
        ]);
        $this->assertDatabaseHas('media_companies', [
            'company_id' => Company::where('name', '=', 'test')->first()->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_that_media_addCompany_method_gives_error_when_company_already_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();
        $company = Company::factory()->create();
        $media->companies()->attach($company->id);
        $this->json('POST', "api/medias/$media->url/companies/$company->name")
            ->assertStatus(400)
            ->assertSee('company already added');
    }

    ////////// removeCompany tests

    public function test_that_media_removeCompnay_method_gives_404_when_company_name_dosent_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/test/companies/test")
            ->assertNotFound()
            ->assertSee('media not found');
        $media = Media::factory()->create();
        $this->json('DELETE', "api/medias/$media->url/companies/test")
            ->assertNotFound()
            ->assertSee('company not found');
    }

    public function test_that_media_removeCompany_method_remove_company_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();
        $company = Company::factory()->create();
        $media->companies()->attach($company->id);
        $this->json('DELETE', "api/medias/$media->url/companies/$company->name")
            ->assertStatus(200);
        $this->assertDatabaseMissing('media_companies', [
            'company_id' => $company->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_that_media_removeCompany_method_gives_error_when_company_not_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $company = Company::factory()->create();
        $media = Media::factory()->create();
        $this->json('DELETE', "api/medias/$media->url/companies/$company->name")
            ->assertStatus(400)
            ->assertSee('company not in media');
    }

    ////////// addLanguage tests

    public function test_that_media_addLanguage_method_404_when_media_url_or_language_name_doesnt_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $this->json('POST', "api/medias/test/languages/test")
            ->assertStatus(404)
            ->assertSee('media not found');

        $media = Media::factory()->create();
        $this->json('POST', "api/medias/$media->url/languages/test")
            ->assertStatus(404)
            ->assertSee('language not found');
    }

    public function test_that_media_addLanguage_method_adds_language_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();

        $language = Language::inRandomOrder()->first();
        $this->json('POST', "api/medias/$media->url/languages/$language->name")
            ->assertCreated();
        $this->assertDatabaseHas('media_languages', [
            'language_id' => $language->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_that_media_addLanguage_method_gives_error_when_language_already_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();
        $language = Language::inRandomOrder()->first();
        $media->languages()->attach($language->id);
        $this->json('POST', "api/medias/$media->url/languages/$language->name")
            ->assertStatus(400)
            ->assertSee('language already added');
    }

    ////////// removeLanguage tests

    public function test_that_media_removeLanguage_method_gives_404_when_language_name_dosent_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/test/languages/test")
            ->assertNotFound()
            ->assertSee('media not found');
        $media = Media::factory()->create();
        $this->json('DELETE', "api/medias/$media->url/languages/test")
            ->assertNotFound()
            ->assertSee('language not found');
    }

    public function test_that_media_removeLanguage_method_remove_language_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();
        $language = Language::inRandomOrder()->first();
        $media->languages()->attach($language->id);
        $this->json('DELETE', "api/medias/$media->url/languages/$language->name")
            ->assertStatus(200);
        $this->assertDatabaseMissing('media_languages', [
            'language_id' => $language->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_that_media_removeLanguage_method_gives_error_when_language_not_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $language = Language::inRandomOrder()->first();
        $media = Media::factory()->create();
        $this->json('DELETE', "api/medias/$media->url/languages/$language->name")
            ->assertStatus(400)
            ->assertSee('language not in media');
    }


    ////////// addCountry tests

    public function test_that_media_addCountry_method_404_when_media_url_or_country_name_doesnt_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);

        $this->json('POST', "api/medias/test/countries/test")
            ->assertStatus(404)
            ->assertSee('media not found');

        $media = Media::factory()->create();
        $this->json('POST', "api/medias/$media->url/countries/test")
            ->assertStatus(404)
            ->assertSee('country not found');
    }

    public function test_that_media_addCountry_method_adds_country_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();

        $country = Country::inRandomOrder()->first();
        $this->json('POST', "api/medias/$media->url/countries/$country->name")
            ->assertCreated();
        $this->assertDatabaseHas('media_countries', [
            'country_id' => $country->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_that_media_addCountry_method_gives_error_when_country_already_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();
        $country = Country::inRandomOrder()->first();
        $media->countries()->attach($country->id);
        $this->json('POST', "api/medias/$media->url/countries/$country->name")
            ->assertStatus(400)
            ->assertSee('country already added');
    }

    ////////// removeCountry tests

    public function test_that_media_removeCountry_method_gives_404_when_country_name_dosent_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/test/countries/test")
            ->assertNotFound()
            ->assertSee('media not found');
        $media = Media::factory()->create();
        $this->json('DELETE', "api/medias/$media->url/countries/test")
            ->assertNotFound()
            ->assertSee('country not found');
    }

    public function test_that_media_removeCountry_method_remove_country_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $media = Media::factory()->create();
        $country = Country::inRandomOrder()->first();
        $media->countries()->attach($country->id);
        $this->json('DELETE', "api/medias/$media->url/countries/$country->name")
            ->assertStatus(200);
        $this->assertDatabaseMissing('media_countries', [
            'country_id' => $country->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_that_media_removeCountry_method_gives_error_when_country_not_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $country = Country::inRandomOrder()->first();
        $media = Media::factory()->create();
        $this->json('DELETE', "api/medias/$media->url/countries/$country->name")
            ->assertStatus(400)
            ->assertSee('country not in media');
    }

    ////////// addPerson tests

    public function test_that_media_addPerson_method_gives_validation_error(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('POST', "api/medias/test/people/test")
            ->assertStatus(422)
            ->assertSee('The job field is required');
    }

    public function test_that_media_addPerson_method_gives_validation_error_on_invalid_jobs(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('POST', "api/medias/test/people/test", ['job' => 'test'])
            ->assertStatus(422)
            ->assertSee('The selected job is invalid');
    }

    public function test_that_media_addPerson_method_gives_404_when_person_or_media_url_doesnt_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $media = Media::factory()->createOne();
        $this->actingAs($adminUser);
        $this->json('POST', "api/medias/test/people/test", ['job' => 'actor'])
            ->assertStatus(404)
            ->assertSee('media not found');

        $this->json('POST', "api/medias/$media->url/people/test", ['job' => 'actor'])
            ->assertStatus(404)
            ->assertSee('person not found');
    }

    public function test_that_media_storePerson_method_adds_person_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $media = Media::factory()->create();
        $this->actingAs($adminUser);

        $jobs = ['actor', 'writer', 'producer', 'music', 'director'];
        $people = Person::factory(5)->create();
        for ($i = 0; $i < count($jobs); $i++) {
            $this->json('POST', "api/medias/$media->url/people/" . $people[$i]->url, ['job' => $jobs[$i]])
                ->assertCreated();
            $this->assertDatabaseHas('media_actors', [
                'person_id' => $people[$i]->id,
                'media_id' => $media->id,
                'job' => $jobs[$i],
            ]);
        }
    }

    public function test_that_media_storePerson_method_gives_error_when_person_already_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $media = Media::factory()->create();
        $this->actingAs($adminUser);
        $person = Person::factory(1)->createOne();
        $media->staff()->attach([
            [
                'person_id' => $person->id,
                'job' => 'actor',
            ]
        ]);
        $this->json('POST', "api/medias/$media->url/people/$person->url", ['job' => 'actor'])
            ->assertStatus(400)
            ->assertSee('person already added');
    }

    /// removePerson tests
    public function test_that_media_removePerson_method_gives_validation_error(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/test/people/test")
            ->assertStatus(422)
            ->assertSee('The job field is required');
    }

    public function test_that_media_removePerson_method_gives_validation_error_on_invalid_jobs(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/test/people/test", ['job' => 'test'])
            ->assertStatus(422)
            ->assertSee('The selected job is invalid');
    }

    public function test_that_media_removePerson_method_gives_404_when_person_or_media_url_doesnt_exist(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $media = Media::factory()->createOne();
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/test/people/test", ['job' => 'actor'])
            ->assertStatus(404)
            ->assertSee('media not found');

        $this->json('DELETE', "api/medias/$media->url/people/test", ['job' => 'actor'])
            ->assertStatus(404)
            ->assertSee('person not found');
    }

    public function test_that_media_removePerson_method_remove_person_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $media = Media::factory()->createOne();
        $this->actingAs($adminUser);
        $person = Person::factory(1)->createOne();
        $media->staff()->attach([
            [
                'person_id' => $person->id,
                'job' => 'actor',
            ]
        ]);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/$media->url/people/$person->url", ['job' => 'actor'])
            ->assertOk();
        $this->assertDatabaseMissing('media_actors', [
            'media_id' => $media->id,
            'person_id' => $person->id,
            'job' => 'actor',
        ]);
    }

    public function test_that_media_removePerson_method_gives_error_when_person_not_added(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $media = Media::factory()->createOne();
        $this->actingAs($adminUser);
        $person = Person::factory(1)->createOne();
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/medias/$media->url/people/$person->url", ['job' => 'actor'])
            ->assertStatus(400);
    }

}
