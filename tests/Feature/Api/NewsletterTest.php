<?php

namespace Tests\Feature\Api;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function test_user_can_subscribe_to_newsletter()
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'email' => $email
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => $email,
            'is_active' => true
        ]);
    }

    /** @test */
    public function test_cannot_subscribe_with_duplicate_email()
    {
        $email = 'duplicate@example.com';
        NewsletterSubscriber::factory()->create(['email' => $email]);

        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'email' => $email
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function test_user_can_unsubscribe_from_newsletter()
    {
        $subscriber = NewsletterSubscriber::create([
            'email' => 'unsubscribe@example.com',
            'unsubscribe_token' => Str::random(32),
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/newsletter/unsubscribe', [
            'email' => $subscriber->email,
            'token' => $subscriber->unsubscribe_token
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => $subscriber->email,
            'is_active' => false
        ]);
    }

    /** @test */
    public function test_admin_can_list_subscribers()
    {
        NewsletterSubscriber::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/newsletter/subscribers');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }
}
