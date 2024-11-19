<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_factory(): void
    {
        $this->assertDatabaseCount(User::class, 0);
        $user = User::factory()->create();
        $this->assertModelExists($user);
        $this->assertDatabaseCount(User::class, 1);
    }

    public function test_user_controller_show_method(): void
    {
        $response = $this->json('GET', "/api/users/test");
        $response->assertNotFound();
        $this->assertJson($response->content());

        $user = User::factory()->create();
        $response = $this->json('GET', "/api/users/$user->username");
        $response->assertOk();
        $this->assertJson($response->content());
        $response->assertJsonStructure(['data' => [
            'name',
            'username',
            'email',
        ]]);
        $jsonContent = json_decode($response->content());
        $this->assertEquals($user->username, $jsonContent->data->username);
        $this->assertEquals($user->name, $jsonContent->data->name);
        $this->assertEquals($user->email, $jsonContent->data->email);
    }

    public function test_user_controller_index_method(): void
    {
        $response = $this->json('GET', '/api/users');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
        $user = User::factory()->create();
        $response = $this->json('GET', '/api/users');
        $response->assertOk();
        $this->assertJson($response->content());
        $response->assertJsonCount(1, 'data');
        $jsonContent = json_decode($response->content());
        $this->assertEquals($user->username, $jsonContent->data[0]->username);
        $this->assertEquals($user->name, $jsonContent->data[0]->name);
        $this->assertEquals($user->email, $jsonContent->data[0]->email);
    }

    public function test_user_controller_index_method_pagination(): void
    {
        $users = User::factory(6)->create();
        $response = $this->json('GET', '/api/users?perpage=5');
        $response->assertOk();
        $this->assertJson($response->content());
        $response->assertJsonCount(5, 'data');
        $response->assertSeeText($users[4]->username);
        $response->assertSeeText($users[4]->email);
        $response->assertDontSeeText($users[5]->username);
        $response->assertDontSeeText($users[5]->email);
    }

    public function test_user_controller_update_method(): void
    {
        $response = $this->json('PUT', "/api/users/test");
        $response->assertUnauthorized();
        $this->assertJson($response->content());

        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->json('PUT', "/api/users/test");
        $response->assertForbidden();
        $this->assertJson($response->content());

        $this->assertDatabaseCount(User::class, 1);
        $response = $this->json('PUT', "/api/users/$user->username", [
            'new_username' => 'new',
            'new_email' => 'new@test.com'
        ]);
        $response->assertOk();
        $this->assertJson($response->content());
        $this->assertDatabaseCount(User::class, 1);
        $this->assertModelExists(User::find($user->id));
        $updateUser = User::find($user->id);
        $this->assertNotEquals($user, $updateUser);
        $this->assertEquals($updateUser->username, 'new');
        $this->assertEquals($updateUser->email, 'new@test.com');
    }

    public function test_user_controller_destroy_method(): void
    {
        $response = $this->json('DELETE', "/api/users/test");
        $response->assertUnauthorized();
        $this->assertJson($response->content());

        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->json('DELETE', "/api/users/test");
        $response->assertForbidden();
        $this->assertJson($response->content());

        $this->assertDatabaseCount(User::class, 1);
        $response = $this->json('DELETE', "/api/users/$user->username");
        $response->assertOk();
        $this->assertJson($response->content());
        $this->assertDatabaseCount(User::class, 0);
    }
}
