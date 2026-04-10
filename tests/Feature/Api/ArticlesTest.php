<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ArticlesTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    /** @test */
    public function test_can_list_published_articles()
    {
        Article::factory()->count(5)->create(['status' => 'published']);
        Article::factory()->count(2)->create(['status' => 'draft']);

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function test_can_show_article_by_slug()
    {
        $article = Article::factory()->create(['status' => 'published', 'slug' => 'test-article']);

        $response = $this->getJson('/api/v1/articles/test-article');

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'test-article');
    }

    /** @test */
    public function test_admin_can_create_article()
    {
        $category = Category::factory()->create();
        $articleData = [
            'title' => 'New Awesome Post',
            'excerpt' => 'This is a short excerpt.',
            'status' => 'published',
            'category_ids' => [$category->id],
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'position' => 1,
                    'content' => ['text' => 'Hello world']
                ]
            ]
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/articles', $articleData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('articles', ['title' => 'New Awesome Post']);
    }

    /** @test */
    public function test_admin_can_update_article()
    {
        $article = Article::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/articles/{$article->id}", [
                'title' => 'Updated Title'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'title' => 'Updated Title']);
    }

    /** @test */
    public function test_admin_can_delete_article()
    {
        $article = Article::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/articles/{$article->id}");

        $response->assertStatus(200);
        // Soft delete check if applicable, or hard delete
        $this->assertSoftDeleted('articles', ['id' => $article->id]);
    }
}
