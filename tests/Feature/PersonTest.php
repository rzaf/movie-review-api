<?php

namespace Tests\Feature;

use App\Models\Following;
use App\Models\Media;
use App\Models\Person;
use App\Models\Country;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\MediaFactory;
use Database\Factories\PersonFactory;
use Database\Factories\UserFactory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new CountrySeeder)->run();
    }

    public function test_that_person_factory_works(): void
    {
        $this->assertDatabaseCount(Person::class, 0);
        $person = Person::factory()->create();
        $this->assertModelExists($person);
        $this->assertDatabaseCount(Person::class, 1);
    }

    public function test_that_person_show_method_gives_404_when_person_url_doesnt_exist(): void
    {
        $response = $this->json('GET', 'api/people/test');
        $response->assertNotFound();
        $this->assertJson($response->content());
    }

    public function test_that_person_show_method_gives_correct_person(): void
    {
        $person = person::factory()->create();
        $response = $this->json('GET', "api/people/$person->url");
        $this->assertJson($response->content());
        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $person->name,
            'country' => Country::find($person->birth_country)->name,
            'gender' => $person->is_male ? 'male' : 'female',
            'about' => $person->about,
            'age' => Carbon::parse($person->birth_date)->age,
            'url' => $person->url,
            'followers_count' => 0,
            'medias_count' => 0,
        ]);
    }

    public function test_that_person_show_method_gives_correct_followers_count(): void
    {
        $person = person::factory()->create();
        $this->assertEquals($person->followers_count, 0);
        UserFactory::times(5)->create()->each(function ($user) use ($person) {
            $person->followers()->attach($user->id);
        });
        $response = $this->json('GET', "api/people/$person->url");
        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $person->name,
            'url' => $person->url,
            'followers_count' => 5,
        ]);
    }

    public function test_that_person_show_method_gives_correct_medias_count(): void
    {
        $person = person::factory()->create();
        $this->assertEquals($person->medias_count, 0);
        (new CategorySeeder)->run();
        MediaFactory::times(5)->create()->each(function ($media) use ($person) {
            $person->medias()->attach($media->id, ['job' => 'actor']);
        });
        $response = $this->json('GET', "api/people/$person->url");
        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $person->name,
            'url' => $person->url,
            'medias_count' => 5,
        ]);
    }

    public function test_that_person_index_method_gives_empty_when_no_people_exist(): void
    {
        $response = $this->json('GET', "api/people")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_that_person_index_method_gives_correct_number_of_people_with_perpage(): void
    {
        person::factory(4)->create();
        $this->json('GET', "api/people")
            ->assertOk()
            ->assertJsonCount(4, 'data');

        person::factory(10)->create();
        $this->json('GET', "api/people?perpage=8")
            ->assertOk()
            ->assertJsonCount(8, 'data');
    }

    public function test_that_person_index_method_filtering_works_correctly(): void
    {
        $people = Person::factory(15)->create();
        $peopleIds = Person::pluck('id');

        UserFactory::times(5)->create()->each(function (User $user) use ($peopleIds) {
            $user->followings()->attach(fake()->randomElements($peopleIds, rand(1, 5)));
        });
        (new CategorySeeder)->run();
        MediaFactory::times(5)->create()->each(function (Media $media) use ($peopleIds) {
            $media->staff()->attach(fake()->randomElements($peopleIds, rand(1, 5)), ['job' => 'actor']);
        });
        $this->json('GET', "api/people?perpage=25&gender=male")
            ->assertOk()
            ->assertJsonCount(Person::where('is_male', '=', 1)->count(), 'data');

        $this->json('GET', "api/people?perpage=25&followers_count=0")
            ->assertOk()
            ->assertJsonCount(Person::has('followers', '=', '0')->count(), 'data');

        $this->json('GET', "api/people?perpage=25&followers_count=1")
            ->assertOk()
            ->assertJsonCount(Person::has('followers', '=', '1')->count(), 'data');

        $this->json('GET', "api/people?perpage=25&followers_count=2")
            ->assertOk()
            ->assertJsonCount(Person::has('followers', '=', '2')->count(), 'data');

        $this->json('GET', "api/people?perpage=25&medias_count=0")
            ->assertOk()
            ->assertJsonCount(Person::has('medias', '=', '0')->count(), 'data');

        $this->json('GET', "api/people?perpage=25&medias_count=1")
            ->assertOk()
            ->assertJsonCount(Person::has('medias', '=', '1')->count(), 'data');

        $this->json('GET', "api/people?perpage=25&medias_count=2")
            ->assertOk()
            ->assertJsonCount(Person::has('medias', '=', '2')->count(), 'data');

        $this->json('GET', "api/people?perpage=25&search_term=a")
            ->assertOk()
            ->assertJsonCount(Person::whereLike('name', 'a')->count(), 'data');

        $this->json('GET', "api/people?perpage=25&search_term=b")
            ->assertOk()
            ->assertJsonCount(Person::whereLike('name', 'b')->count(), 'data');

        $this->json('GET', "api/people?perpage=25&search_term=1")
            ->assertOk()
            ->assertJsonCount(Person::whereLike('name', '1')->count(), 'data');
    }

    public function test_that_person_store_method_validates_inputs_correctly(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $this->json('POST', "api/people", [])
            ->assertSee('The name field is required')
            ->assertSee('The url field is required')
            ->assertSee('The country field is required')
            ->assertSee('The is male field is required')
            ->assertSee('The birth date field is required')
            ->assertStatus(422)
            ->assertJsonCount(5, 'errors');

        $this->json('POST', "api/people", [
            'name' => 'test',
        ])->assertDontSee('The name field is required')
            ->assertSee('The url field is required')
            ->assertSee('The country field is required')
            ->assertSee('The is male field is required')
            ->assertSee('The birth date field is required')
            ->assertStatus(422)
            ->assertJsonCount(4, 'errors');

        $this->json('POST', "api/people", [
            'name' => 'test',
            'url' => 'test',
        ])->assertDontSee('The name field is required')
            ->assertDontSee('The url field is required')
            ->assertSee('The country field is required')
            ->assertSee('The is male field is required')
            ->assertSee('The birth date field is required')
            ->assertStatus(422)
            ->assertJsonCount(3, 'errors');

        $this->json('POST', "api/people", [
            'name' => 'test',
            'url' => 'test',
            'country' => 'us',
        ])->assertDontSee('The name field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The country field is required')
            ->assertSee('The is male field is required')
            ->assertSee('The birth date field is required')
            ->assertStatus(422)
            ->assertJsonCount(2, 'errors');

        $this->json('POST', "api/people", [
            'name' => 'test',
            'url' => 'test',
            'country' => 'us',
            'is_male' => 1,
        ])->assertDontSee('The name field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The country field is required')
            ->assertDontSee('The is male field is required')
            ->assertSee('The birth date field is required')
            ->assertStatus(422)
            ->assertJsonCount(1, 'errors');

        $this->json('POST', "api/people", [
            'name' => 'test',
            'url' => 'test',
            'country' => 'us',
            'is_male' => 1,
            'birth_date' => '2020-11-1',
        ])->assertStatus(201);
    }

    public function test_that_person_store_method_creates_person_correctly(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $person = PersonFactory::times(1)->makeOne();
        $this->assertDatabaseMissing(Person::class, $person->toArray());
        $response = $this->json('POST', "api/people", array_merge($person->toArray(), [
            'country' => Country::find($person->birth_country)->name,
        ]));
        $response->assertCreated();
        $this->assertDatabaseCount(person::class, 1);
        $this->assertDatabaseHas(person::class, $person->toArray());
        $response->assertJsonFragment([
            'name' => $person->name,
            'gender' => $person->is_male ? 'male' : 'female',
            'about' => $person->about,
            'age' => Carbon::parse($person->birth_date)->age,
            'url' => $person->url,
        ]);
    }

    public function test_that_person_store_method_gives_error_on_duplicate_url(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $person = PersonFactory::times(1)->createOne();
        $newPerson = PersonFactory::times(1)->makeOne();
        $newPerson->url = $person->url;
        $this->actingAs($adminUser);
        $this->json('POST', "api/people", array_merge($newPerson->toArray(), [
            'country' => Country::find($newPerson->birth_country)->name,
        ]))->assertSee('duplicate url')
            ->assertStatus(400);
    }

    public function test_that_person_update_method_validates_inputs_correctly(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $this->json('PUT', "api/people/test", [])
            ->assertSee('The name field is required')
            ->assertDontSee('The url field is required')
            ->assertDontSee('The country field is required')
            ->assertDontSee('The is male field is required')
            ->assertDontSee('The birth date field is required')
            ->assertStatus(422)
            ->assertJsonCount(1, 'errors');
    }

    public function test_that_person_update_method_gives_404__when_person_url_doesnt_exist(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $this->json('PUT', "api/people/test", [
            'name' => 'test'
        ])->assertNotFound();
    }

    public function test_that_person_update_method_updates_person_correctly(): void
    {
        $person = Person::factory()->create();
        $newPerson = Person::factory()->makeOne(['is_male' => $person->is_male]);
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $this->json('PUT', "api/people/$person->url", array_merge($newPerson->toArray(), [
            'country' => Country::find($newPerson->birth_country)->name,
        ]))
            ->assertOk();
        $this->assertDatabaseMissing(Person::class, $person->toArray());
        $this->assertDatabaseHas(Person::class, $newPerson->toArray());
    }

    public function test_that_person_update_method_gives_error_on_duplicate_url(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $oldPerson = PersonFactory::times(1)->createOne();
        $person = PersonFactory::times(1)->createOne();
        $this->json('PUT', "api/people/$person->url", [
            'name' => 'test',
            'url' => $oldPerson->url,
        ])
            ->assertSee('duplicate url')
            ->assertStatus(400);
    }

    public function test_that_person_destroy_method_gives_404__when_person_url_doesnt_exist(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/people/test")
            ->assertNotFound();
    }

    public function test_that_person_destroy_method_deletes_existing_person_correctly(): void
    {
        $person = Person::factory()->create();
        $adminUser = User::factory()->create(['role' => 'admin',]);
        $this->actingAs($adminUser);
        $this->assertModelExists($person);
        $response = $this->json('DELETE', "api/people/$person->url");
        $response->assertOk();
        $this->assertModelMissing($person);
    }

    public function test_that_person_storeFollowing_method_gives_404_when_preson_url_dosent_exist(): void
    {
        $normalUser = User::factory()->create();
        $this->actingAs($normalUser);
        $this->json('POST', "api/people/test/following")
            ->assertNotFound();
    }

    public function test_that_person_storeFollowing_method_adds_following_correctly(): void
    {
        $person = Person::factory()->create();
        $normalUser = User::factory()->create();
        $this->actingAs($normalUser);
        $this->json('POST', "api/people/$person->url/following")
            ->assertOk();
        $this->assertDatabaseHas(Following::class, [
            'follower_id' => $normalUser->id,
            'following_id' => $person->id,
        ]);
    }

    public function test_that_person_storeFollowing_method_gives_error_when_following_already_added(): void
    {
        $normalUser = User::factory()->create();
        $person = Person::factory()->create();
        $person->followers()->attach($normalUser->id);
        $this->actingAs($normalUser);
        $this->json('POST', "api/people/$person->url/following")
            ->assertStatus(400)
            ->assertSee('already followed');
    }

    public function test_that_person_destroyFollowing_method_gives_404_when_person_url_dosent_exist(): void
    {
        $normalUser = User::factory()->create();
        $this->actingAs($normalUser);
        $this->json('DELETE', "api/people/test/following")
            ->assertNotFound();
    }

    public function test_that_person_destroyFollowing_method_remove_following_correctly(): void
    {
        $normalUser = User::factory()->create();
        $person = Person::factory()->create();
        $person->followers()->attach($normalUser->id);
        $this->actingAs($normalUser);
        $this->json('DELETE', "api/people/$person->url/following")
            ->assertStatus(200);
        $this->assertDatabaseMissing(Following::class,[
            'follower_id' => $normalUser->id,
            'following_id' => $person->id,
        ]);
    }

    public function test_that_person_destroyFollowing_method_gives_error_when_person_not_followed(): void
    {
        $normalUser = User::factory()->create();
        $person = Person::factory()->create();
        $this->actingAs($normalUser);
        $this->json('DELETE', "api/people/$person->url/following")
            ->assertStatus(400);
    }
}
