<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_up(): void
    {
        $response = $this->json('POST', '/api/users', [
            'email' => 'testing@test.com',
            'name' => 'test',
            'username' => 'testing',
            'password' => '1234',
            'password_confirmation' => '1234',
        ]);
        $response->assertCreated();
        $this->assertNotNull(User::where('username', '=', 'testing')->first());
    }


    public function test_login(): void
    {
        $response = $this->json('POST', '/api/login', [
            'username' => 'test',
            'password' => '1234',
            'password_confirmation' => '1234',
        ]);
        $response->assertUnauthorized();
        $response->assertSeeText(['message' => 'invalid username or password']);

        $user = User::factory()->create();

        $response = $this->json('POST', '/api/login', [
            'username' => 'incorrect_username',
            'password' => '1234',
            'password_confirmation' => '1234',
        ]);
        $response->assertUnauthorized();
        $response->assertSeeText(['message' => 'invalid username or password']);

        $response = $this->json('POST', '/api/login', [
            'username' => $user->username,
            'password' => 'incorrect_pass',
            'password_confirmation' => 'incorrect_pass',
        ]);
        $response->assertUnauthorized();
        $response->assertSeeText(['message' => 'invalid username or password']);

        $response = $this->json('POST', '/api/login', [
            'username' => $user->username,
            'password' => '1234',
            'password_confirmation' => '1234',
        ]);
        $response->assertOk();
        $response->assertJsonStructure(['new token', 'user']);

        $token = json_decode($response->content(), true)['new token'];
        $response = $this->json('GET', 'api/user', headers: [
            'authorization' => $token,
        ]);
        $response->assertOk();
    }
}
