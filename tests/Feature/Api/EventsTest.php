<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EventsTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected User $admin;
    protected User $editor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->editor = User::factory()->create(['role' => 'editor']);
    }

    /** @test */
    public function test_can_list_events()
    {
        Event::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/events');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'title', 'slug', 'status']
                ],
                'message',
                'meta' => ['total']
            ]);
    }

    /** @test */
    public function test_can_show_event_by_slug()
    {
        $event = Event::factory()->create(['slug' => 'test-event']);

        $response = $this->getJson('/api/v1/events/test-event');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $event->id);
    }

    /** @test */
    public function test_admin_can_create_event()
    {
        $eventData = [
            'title' => 'New Conference',
            'description' => '<p>Event description</p>',
            'location' => 'Paris',
            'event_date' => now()->addDays(10)->toDateTimeString(),
            'type' => 'conference',
            'status' => 'upcoming'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/events', $eventData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('events', ['title' => 'New Conference']);
    }

    /** @test */
    public function test_editor_cannot_create_event()
    {
        $response = $this->actingAs($this->editor, 'sanctum')
            ->postJson('/api/v1/admin/events', []);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_admin_can_update_event()
    {
        $event = Event::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/events/{$event->id}", [
                'title' => 'Updated Title'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('events', ['id' => $event->id, 'title' => 'Updated Title']);
    }

    /** @test */
    public function test_admin_can_delete_event()
    {
        $event = Event::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/events/{$event->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    /** @test */
    public function test_user_can_like_event()
    {
        $event = Event::factory()->create(['likes_count' => 0]);

        $response = $this->postJson("/api/v1/events/{$event->id}/like");

        $response->assertStatus(200)
            ->assertJsonPath('data.likesCount', 1);
        
        $this->assertEquals(1, $event->fresh()->likes_count);
    }
}
