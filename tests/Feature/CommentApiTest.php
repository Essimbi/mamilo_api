<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comment Endpoints Tests
 */
class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: POST /articles/{id}/comments - Create comment
     */
    public function test_can_create_comment_on_article(): void
    {
        // Arrange
        $article = Article::factory()->create();
        $data = [
            'author_name' => 'John Doe',
            'content' => 'Great article!',
        ];

        // Act
        $response = $this->postJson("/api/v1/articles/{$article->id}/comments", $data);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', ['author_name' => 'John Doe']);
    }

    /**
     * Test: GET /articles/{id}/comments - Get article comments
     */
    public function test_can_get_article_comments(): void
    {
        // Arrange
        $article = Article::factory()->create();
        Comment::factory(3)->create([
            'commentable_type' => Article::class,
            'commentable_id' => $article->id,
            'is_approved' => true,
        ]);

        // Act
        $response = $this->getJson("/api/v1/articles/{$article->id}/comments");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Test: Comment validation - Author name required
     */
    public function test_comment_requires_author_name(): void
    {
        // Arrange
        $article = Article::factory()->create();
        $data = [
            'content' => 'Comment without name',
        ];

        // Act
        $response = $this->postJson("/api/v1/articles/{$article->id}/comments", $data);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['author_name']);
    }

    /**
     * Test: Comment validation - Content too short
     */
    public function test_comment_content_minimum_length(): void
    {
        // Arrange
        $article = Article::factory()->create();
        $data = [
            'author_name' => 'Jane',
            'content' => 'Too',
        ];

        // Act
        $response = $this->postJson("/api/v1/articles/{$article->id}/comments", $data);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    /**
     * Test: POST /events/{id}/comments - Create comment on event
     */
    public function test_can_create_comment_on_event(): void
    {
        // Arrange
        $event = Event::factory()->create();
        $data = [
            'author_name' => 'Alice',
            'content' => 'Great event!',
        ];

        // Act
        $response = $this->postJson("/api/v1/events/{$event->id}/comments", $data);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', ['author_name' => 'Alice']);
    }

    /**
     * Test: GET /events/{id}/comments - Get event comments
     */
    public function test_can_get_event_comments(): void
    {
        // Arrange
        $event = Event::factory()->create();
        Comment::factory(2)->create([
            'commentable_type' => Event::class,
            'commentable_id' => $event->id,
            'is_approved' => true,
        ]);

        // Act
        $response = $this->getJson("/api/v1/events/{$event->id}/comments");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }
}
