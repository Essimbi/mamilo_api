<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected User $admin;
    protected Article $article;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->article = Article::factory()->create(['status' => 'published']);
    }

    /** @test */
    public function test_user_can_submit_comment()
    {
        $commentData = [
            'author_name' => 'John Doe',
            'author_avatar' => 'https://example.com/avatar.png',
            'content' => 'This is a test comment.'
        ];

        $response = $this->postJson("/api/v1/articles/{$this->article->id}/comments", $commentData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', [
            'article_id' => $this->article->id,
            'author_name' => 'John Doe',
            'is_approved' => false
        ]);
    }

    /** @test */
    public function test_admin_can_list_comments()
    {
        Comment::factory()->count(3)->create(['article_id' => $this->article->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/comments');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function test_admin_can_approve_comment()
    {
        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'is_approved' => false
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/comments/{$comment->id}/approve");

        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'is_approved' => true
        ]);
    }

    /** @test */
    public function test_admin_can_delete_comment()
    {
        $comment = Comment::factory()->create(['article_id' => $this->article->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }
}
