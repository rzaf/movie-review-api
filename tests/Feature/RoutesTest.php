<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Route;
use Tests\TestCase;

class RoutesTest extends TestCase
{
    use RefreshDatabase;

    private $publicRoutes = [
        '/users' => 'get,post',
        '/users/{username}' => 'get',
        '/login' => 'post',

        '/categories' => 'get',
        '/categories/{name}' => 'get',

        '/people' => 'get',
        '/people/{url}/medias' => 'get',
        '/people/{url}' => 'get',

        '/medias' => 'get',
        '/medias/{url}' => 'get',

        '/reviews/{review_id}' => 'get',
        '/medias/{media_url}/reviews' => 'get',

        '/replies/{reply_id}' => 'get',
        '/reviews/{review_id}/replies' => 'get',
        '/replies/{reply_id}/replies' => 'get',
    ];
    private $protectedRoutes = [
        '/user' => 'get',
        '/users/{username}' => 'put,delete',

        '/categories' => 'post',
        '/categories/{name}' => 'put,delete',

        '/people' => 'post',
        '/people/{url}' => 'put,delete',
        '/people/{url}/following' => 'post,delete',

        '/medias' => 'post',
        '/medias/{url}' => 'put,delete',
        '/medias/{media_url}/like' => 'post,delete',
        '/medias/{media_url}/people/{person_url}' => 'post,delete',
        '/medias/{media_url}/genres/{name}' => 'post,delete',
        '/medias/{media_url}/countries/{name}' => 'post,delete',
        '/medias/{media_url}/languages/{name}' => 'post,delete',
        '/medias/{media_url}/keywords/{name}' => 'post,delete',
        '/medias/{media_url}/companies/{name}' => 'post,delete',

        '/medias/{media_url}/reviews' => 'post',
        '/reviews/{review_id}' => 'put,delete',
        '/reviews/{review_id}/like' => 'post,delete',

        '/replies' => 'post',
        '/replies/{reply_id}' => 'put,delete',
        '/replies/{reply_id}/like' => 'post,delete',
    ];

    public function test_that_all_routes_exist_in_routes_files(): void
    {
        $allRoutes = Route::getRoutes()->getRoutesByMethod();
        foreach ($this->publicRoutes as $route => $methods) {
            $route = "api$route";
            foreach (explode(',', $methods) as $method) {
                $method = strtoupper($method);
                $routeObject = $allRoutes[$method][$route] ?? null;
                $this->assertNotNull($routeObject);
            }
        }
        foreach ($this->protectedRoutes as $route => $methods) {
            $route = "api$route";
            foreach (explode(',', $methods) as $method) {
                $method = strtoupper($method);
                $routeObject = $allRoutes[$method][$route] ?? null;
                $this->assertNotNull($routeObject);
            }
        }
    }

    public function test_that_public_routes_dont_have_auth_middleware(): void
    {
        $allRoutes = Route::getRoutes()->getRoutesByMethod();
        foreach ($this->publicRoutes as $route => $methods) {
            $route = "api$route";
            foreach (explode(',', $methods) as $method) {
                $method = strtoupper($method);
                $routeObject = $allRoutes[$method][$route] ?? null;
                $middelwares = $routeObject->middleware();
                $this->assertTrue(!in_array('auth:sanctum', $middelwares));
            }
        }
    }

    public function test_that_protected_routes_have_auth_middleware(): void
    {
        $allRoutes = Route::getRoutes()->getRoutesByMethod();
        foreach ($this->protectedRoutes as $route => $methods) {
            $route = "api$route";
            foreach (explode(',', $methods) as $method) {
                $method = strtoupper($method);
                $routeObject = $allRoutes[$method][$route] ?? null;
                $middelwares = $routeObject->middleware();
                $this->assertTrue(in_array('auth:sanctum', $middelwares));
            }
        }
    }

    public function test_that_all_routes_exist_and_allowed(): void
    {
        foreach ($this->publicRoutes as $route => $methods) {
            $route = "/api$route";
            // echo "route: $route\n";
            foreach (explode(',', $methods) as $method) {
                $response = $this->json($method, $route);
                $response->assertDontSee('The route');
                $this->assertTrue($response->status() != 405); //method not found
            }
        }
        foreach ($this->protectedRoutes as $route => $methods) {
            $route = "/api$route";
            // echo "route: $route\n";
            foreach (explode(',', $methods) as $method) {
                $response = $this->json($method, $route);
                $response->assertDontSee('The route');
                $this->assertTrue($response->status() != 405); //method not found
            }
        }
    }

    public function test_that_public_routes_dont_require_authentication(): void
    {
        foreach ($this->publicRoutes as $route => $methods) {
            $route = "/api$route";
            // echo "route: $route\n";
            foreach (explode(',', $methods) as $method) {
                $response = $this->json($method, $route);
                // echo $response->content();
                $status = $response->status();
                $this->assertTrue($status != 401);
                $this->assertTrue($status != 403);
            }
        }
    }

    public function test_that_protected_routes_require_authentication(): void
    {
        foreach ($this->protectedRoutes as $route => $methods) {
            $route = "/api$route";
            foreach (explode(',', $methods) as $method) {
                $this->json($method, $route)
                    ->assertUnauthorized()
                    ->assertSee('Unauthenticated.');
            }
        }
    }
}
