<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Event;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CommentService Tests
 */
class CommentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CommentService::class);
    }

    /**
     * Test: Create comment for article
     */
    public function test_create_comment_for_article(): void
    {
        // Arrange
        $article = Article::factory()->create();
        $data = [
            'author_name' => 'John Doe',
            'content' => 'Great article!',
        ];

        // Act
        $comment = $this->service->createForArticle($article, $data);

        // Assert
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('John Doe', $comment->author_name);
        $this->assertFalse($comment->is_approved);
    }

    /**
     * Test: Get approved comments
     */
    public function test_get_approved_comments(): void
    {
        // Arrange
        $article = Article::factory()->create();
        Comment::factory(2)->create([
            'commentable_type' => Article::class,
            'commentable_id' => $article->id,
            'is_approved' => true,
        ]);
        Comment::factory()->create([
            'commentable_type' => Article::class,
            'commentable_id' => $article->id,
            'is_approved' => false,
        ]);

        // Act
        $comments = $this->service->getArticleComments($article);

        // Assert
        $this->assertCount(2, $comments);
        $this->assertTrue($comments->every(fn($c) => $c->is_approved));
    }

    /**
     * Test: Approve comment
     */
    public function test_approve_comment(): void
    {
        // Arrange
        $comment = Comment::factory()->create(['is_approved' => false]);

        // Act
        $approved = $this->service->approve($comment);

        // Assert
        $this->assertTrue($approved->is_approved);
    }

    /**
     * Test: Reject comment
     */
    public function test_reject_comment(): void
    {
        // Arrange
        $comment = Comment::factory()->create();

        // Act
        $result = $this->service->reject($comment);

        // Assert
        $this->assertTrue($result);
        $this->assertNull(Comment::find($comment->id));
    }

    /**
     * Test: Get pending comments for moderation
     */
    public function test_get_pending_comments(): void
    {
        // Arrange
        Comment::factory(2)->create(['is_approved' => false]);
        Comment::factory(3)->create(['is_approved' => true]);

        // Act
        $pending = $this->service->getPending();

        // Assert
        $this->assertCount(2, $pending);
        $this->assertTrue($pending->every(fn($c) => !$c->is_approved));
    }
}
