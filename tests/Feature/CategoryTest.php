<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Factories\MediaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_that_category_factory_works(): void
    {
        $this->assertDatabaseCount(Category::class, 0);
        $category = Category::factory()->create();
        $this->assertModelExists($category);
        $this->assertDatabaseCount(Category::class, 1);
    }

    public function test_that_category_show_method_gives_404_when_category_name_doesnt_exist(): void
    {
        $response = $this->json('GET', 'api/categories/test');
        $response->assertNotFound();
        $this->assertJson($response->content());
    }

    public function test_that_category_show_method_gives_correct_category(): void
    {
        $category = Category::factory()->create();
        $response = $this->json('GET', "api/categories/$category->name");
        $this->assertJson($response->content());
        $response->assertOk();
        $jsonContent = json_decode($response->content());
        $this->assertEquals($category->name, $jsonContent->data->name);
        $this->assertEquals(0, $jsonContent->data->medias_count);
    }

    public function test_that_category_show_method_gives_correct_medias_count(): void
    {
        $category = Category::factory()->create();
        MediaFactory::times(5)->create([
            'category_id' => $category->id,
        ]);
        $response = $this->json('GET', "api/categories/$category->name");
        $this->assertEquals($category->medias_count, 0);
        $response->assertOk();
        $jsonContent = json_decode($response->content());
        $this->assertEquals($category->name, $jsonContent->data->name);
        $this->assertEquals(5, $jsonContent->data->medias_count);
    }

    public function test_that_category_index_method_gives_empty_when_no_categories_exist(): void
    {
        $this->json('GET', "api/categories")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_that_category_index_method_gives_correct_number_of_categories_with_perpage(): void
    {
        Category::factory(4)->create();
        $this->json('GET', "api/categories")
            ->assertOk()
            ->assertJsonCount(4, 'data');

        Category::factory(10)->create();
        $this->json('GET', "api/categories?perpage=8")
            ->assertOk()
            ->assertJsonCount(8, 'data');
    }

    public function test_that_category_store_method_creates_category_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $response = $this->json('POST', "api/categories", ['name' => 'test']);
        $response->assertCreated();
        $this->assertDatabaseCount(Category::class, 1);
        $createdCategory = Category::first();
        $this->assertNotNull($createdCategory);
        $this->assertEquals('test', $createdCategory->name);
    }

    public function test_that_category_update_method_updates_category_correctly(): void
    {
        $category = Category::factory()->create();
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->actingAs($adminUser);
        $this->json('PUT', "api/categories/$category->name", ['name' => 'test',])
            ->assertOk();
        $newCategory = Category::first();
        $this->assertNotNull($newCategory);
        $this->assertEquals('test', $newCategory->name);
        $this->assertNotEquals($newCategory->name, $category->name);
    }

    public function test_that_category_destroy_method_gives_404__when_category_name_doesnt_exist(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
        $this->json('DELETE', "api/categories/test")
            ->assertNotFound();
    }

    public function test_that_category_destroy_method_deletes_existing_category_correctly(): void
    {
        $category = Category::factory()->create();
        $adminUser = User::factory()->create(['role' => 'admin',]);
        $this->actingAs($adminUser);
        $this->assertModelExists($category);
        $response = $this->json('DELETE', "api/categories/$category->name");
        $response->assertOk();
        $this->assertModelMissing($category);
    }
}
