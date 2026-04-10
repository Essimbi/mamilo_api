<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleService;
use App\Services\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ArticleService Tests
 * 
 * Test toutes les fonctionnalités du service ArticleService
 */
class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ArticleService $service;
    protected ContentService $contentService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentService = app(ContentService::class);
        $this->service = new ArticleService($this->contentService);
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test: Get published articles
     */
    public function test_get_published_articles(): void
    {
        // Arrange
        $published = Article::factory(3)->create(['status' => 'published', 'published_at' => now()]);
        Article::factory(2)->create(['status' => 'draft', 'published_at' => null]);

        // Act
        $articles = $this->service->getPublished();

        // Assert
        $this->assertCount(3, $articles);
        $this->assertTrue($articles->every(fn($a) => $a->status === 'published'));
    }

    /**
     * Test: Create article with blocks
     */
    public function test_create_article_with_blocks(): void
    {
        // Arrange
        $data = [
            'title' => 'Test Article',
            'excerpt' => 'Test excerpt',
            'status' => 'published',
            'author_id' => $this->user->id,
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Block 1', 'position' => 1],
                ['type' => 'paragraph', 'content' => 'Block 2', 'position' => 2],
            ],
        ];

        // Act
        $article = $this->service->create($data);

        // Assert
        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('Test Article', $article->title);
        $this->assertCount(2, $article->blocks);
    }

    /**
     * Test: Like article
     */
    public function test_like_article(): void
    {
        // Arrange
        $article = Article::factory()->create(['likes_count' => 0]);

        // Act
        $likesCount = $this->service->like($article);

        // Assert
        $this->assertEquals(1, $likesCount);
        $this->assertEquals(1, $article->fresh()->likes_count);
    }

    /**
     * Test: Unlike article
     */
    public function test_unlike_article(): void
    {
        // Arrange
        $article = Article::factory()->create(['likes_count' => 5]);

        // Act
        $likesCount = $this->service->unlike($article);

        // Assert
        $this->assertEquals(4, $likesCount);
        $this->assertEquals(4, $article->fresh()->likes_count);
    }

    /**
     * Test: Update article
     */
    public function test_update_article(): void
    {
        // Arrange
        $article = Article::factory()->create(['title' => 'Old Title']);
        $updates = ['title' => 'New Title', 'excerpt' => 'New excerpt'];

        // Act
        $updated = $this->service->update($article, $updates);

        // Assert
        $this->assertEquals('New Title', $updated->title);
        $this->assertEquals('New excerpt', $updated->excerpt);
    }

    /**
     * Test: Delete article
     */
    public function test_delete_article(): void
    {
        // Arrange
        $article = Article::factory()->create();

        // Act
        $result = $this->service->delete($article);

        // Assert
        $this->assertTrue($result);
        $this->assertNull(Article::find($article->id));
    }

    /**
     * Test: Get article by slug
     */
    public function test_get_article_by_slug(): void
    {
        // Arrange
        $article = Article::factory()->create(['slug' => 'test-article', 'status' => 'published']);

        // Act
        $retrieved = $this->service->getBySlug('test-article');

        // Assert
        $this->assertEquals($article->id, $retrieved->id);
        $this->assertEquals('test-article', $retrieved->slug);
    }
}
