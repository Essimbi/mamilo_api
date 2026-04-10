<?php

namespace Tests\Feature\Api;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->admin = User::factory()->create(['role' => 'admin']);
        Storage::fake('public');
    }

    /** @test */
    public function test_admin_can_upload_media()
    {
        $file = UploadedFile::fake()->image('test_image.jpg', 800, 600);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/media/upload', [
                'file' => $file,
                'alt_text' => 'Test Alt Text',
                'caption' => 'Test Caption'
            ]);

        $response->dump();
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('media', [
            'alt_text' => 'Test Alt Text',
            'caption' => 'Test Caption'
        ]);

        $media = Media::latest()->first();
        Storage::disk('public')->assertExists($media->path);
        if ($media->thumbnail_path) {
            Storage::disk('public')->assertExists($media->thumbnail_path);
        }
    }

    /** @test */
    public function test_admin_can_list_media()
    {
        Media::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/media');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function test_admin_can_delete_media()
    {
        $media = Media::factory()->create();
        
        // Mock file existence
        Storage::disk('public')->put($media->path, 'test content');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/media/{$media->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        
        // Note: The physical file deletion depends on the implementation of Media model delete event.
        // If not implemented, it won't delete. Let's check the controller/model.
    }

    /** @test */
    public function test_non_admin_cannot_upload_media()
    {
        $user = User::factory()->create(['role' => 'user']);
        $file = UploadedFile::fake()->image('test_image.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/admin/media/upload', [
                'file' => $file
            ]);

        $response->assertStatus(403);
    }
}
