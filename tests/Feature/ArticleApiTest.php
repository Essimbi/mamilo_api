<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

/**
 * Article Endpoints Tests
 * 
 * Test tous les endpoints relatifs aux articles
 */
class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->editor = User::factory()->create(['role' => 'editor']);
    }

    /**
     * Test: GET /articles - List articles
     */
    public function test_can_list_articles(): void
    {
        // Arrange
        Article::factory(5)->create(['status' => 'published', 'published_at' => now()]);

        // Act
        $response = $this->getJson('/api/v1/articles');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => ['*' => ['id', 'title', 'slug']],
            'message',
        ]);
    }

    /**
     * Test: GET /articles?search=term - Search articles
     */
    public function test_can_search_articles(): void
    {
        // Arrange
        Article::factory()->create([
            'title' => 'Laravel Tips',
            'status' => 'published',
            'published_at' => now(),
        ]);
        Article::factory()->create([
            'title' => 'PHP Basics',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Act
        $response = $this->getJson('/api/v1/articles?search=Laravel');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /**
     * Test: POST /admin/articles - Create article (Requires auth)
     */
    public function test_can_create_article_authenticated(): void
    {
        // Arrange
        $data = [
            'title' => 'New Article',
            'excerpt' => 'Article excerpt',
            'status' => 'published',
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Content', 'position' => 1],
            ],
        ];

        // Act
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/articles', $data);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'data' => ['id', 'title'], 'message']);
        $this->assertDatabaseHas('articles', ['title' => 'New Article']);
    }

    /**
     * Test: POST /admin/articles - Unauthenticated fails
     */
    public function test_cannot_create_article_unauthenticated(): void
    {
        // Arrange
        $data = [
            'title' => 'New Article',
            'status' => 'published',
        ];

        // Act
        $response = $this->postJson('/api/v1/admin/articles', $data);

        // Assert
        $response->assertStatus(401);
    }

    /**
     * Test: GET /articles/{slug} - Get article by slug
     */
    public function test_can_get_article_by_slug(): void
    {
        // Arrange
        $article = Article::factory()->create([
            'slug' => 'test-article',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Act
        $response = $this->getJson('/api/v1/articles/test-article');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.slug', 'test-article');
    }

    /**
     * Test: PUT /admin/articles/{id} - Update article
     */
    public function test_can_update_article(): void
    {
        // Arrange
        $article = Article::factory()->create();
        $updates = ['title' => 'Updated Title'];

        // Act
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/articles/{$article->id}", $updates);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'title' => 'Updated Title']);
    }

    /**
     * Test: DELETE /admin/articles/{id} - Delete article
     */
    public function test_can_delete_article(): void
    {
        // Arrange
        $article = Article::factory()->create();

        // Act
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/admin/articles/{$article->id}");

        // Assert
        $response->assertStatus(200);
        $this->assertSoftDeleted('articles', ['id' => $article->id]);
    }

    /**
     * Test: POST /articles/{id}/like - Like article
     */
    public function test_can_like_article(): void
    {
        // Arrange
        $article = Article::factory()->create(['likes_count' => 0]);

        // Act
        $response = $this->postJson("/api/v1/articles/{$article->id}/like");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.likesCount', 1);
    }

    /**
     * Test: DELETE /articles/{id}/like - Unlike article
     */
    public function test_can_unlike_article(): void
    {
        // Arrange
        $article = Article::factory()->create(['likes_count' => 5]);

        // Act
        $response = $this->deleteJson("/api/v1/articles/{$article->id}/like");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.likesCount', 4);
    }
}
