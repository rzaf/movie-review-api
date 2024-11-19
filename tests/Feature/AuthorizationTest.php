<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminOnly;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Route;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private $adminRoutes = [
        '/categories' => 'post',
        '/categories/{name}' => 'put,delete',

        '/people' => 'post',
        '/people/{url}' => 'put,delete',

        '/medias' => 'post',
        '/medias/{url}' => 'put,delete',
        '/medias/{media_url}/people/{person_url}' => 'post,delete',
        '/medias/{media_url}/genres/{name}' => 'post,delete',
        '/medias/{media_url}/countries/{name}' => 'post,delete',
        '/medias/{media_url}/languages/{name}' => 'post,delete',
        '/medias/{media_url}/keywords/{name}' => 'post,delete',
        '/medias/{media_url}/companies/{name}' => 'post,delete',

        // '/medias/{media_url}/reviews' => 'post',
        // '/reviews/{review_id}' => 'put,delete',

        // '/replies' => 'post',
        // '/replies/{reply_id}' => 'put,delete',
    ];

    public function test_that_admin_routes_give_401_for_unauthenticated_client(): void
    {
        foreach ($this->adminRoutes as $route => $methods) {
            $route = "/api$route";
            foreach (explode(',', $methods) as $method) {
                $this->json($method, $route)
                    ->assertUnauthorized()
                    ->assertSee('Unauthenticated.');
            }
        }
    }

    public function test_that_admin_routes_give_403_for_normal_users(): void
    {
        $normalUser = User::factory(1)->createOne(['role' => 'normal']);
        $this->actingAs($normalUser);
        foreach ($this->adminRoutes as $route => $methods) {
            $route = "/api$route";
            foreach (explode(',', $methods) as $method) {
                $this->json($method, $route)
                    ->assertSee('Only admins are authorized for this action')
                    ->assertForbidden();
            }
        }
    }

    public function test_that_admin_routes_dont_give_403_for_admins(): void
    {
        $adminUser = User::factory(1)->createOne(['role' => 'admin']);
        $this->actingAs($adminUser);
        foreach ($this->adminRoutes as $route => $methods) {
            $route = "/api$route";
            foreach (explode(',', $methods) as $method) {
                $response = $this->json($method, $route)
                    ->assertDontSee('Unauthenticated.');
                $this->assertNotEquals(403, $response->getStatusCode());
            }
        }
    }

    public function test_that_admin_routes_have_auth_and_adminOnly_middlewares(): void
    {
        $allRoutes = Route::getRoutes()->getRoutesByMethod();
        foreach ($this->adminRoutes as $route => $methods) {
            $route = "api$route";
            foreach (explode(',', $methods) as $method) {
                $method = strtoupper($method);
                $routeObject = $allRoutes[$method][$route] ?? null;
                $middelwares = $routeObject->middleware();
                $this->assertTrue(in_array('auth:sanctum', $middelwares));
                $this->assertTrue(in_array(AdminOnly::class, $middelwares));
            }
        }
    }
}
