<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function test_can_list_settings()
    {
        Setting::updateOrCreate(['key' => 'site_name'], ['value' => 'Mamilo Blog']);
        Setting::updateOrCreate(['key' => 'site_description'], ['value' => 'Test description']);

        $response = $this->getJson('/api/v1/settings');

        $response->assertStatus(200)
            ->assertJsonFragment(['key' => 'site_name', 'value' => 'Mamilo Blog'])
            ->assertJsonFragment(['key' => 'site_description', 'value' => 'Test description']);
    }

    /** @test */
    public function test_admin_can_update_settings()
    {
        $settingsData = [
            'site_name' => 'Updated Blog Name',
            'site_description' => 'Updated Description',
            'contact_email' => 'admin@mamilo.com'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/v1/admin/settings', $settingsData);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'Updated Blog Name']);
        $this->assertDatabaseHas('settings', ['key' => 'site_description', 'value' => 'Updated Description']);
        $this->assertDatabaseHas('settings', ['key' => 'contact_email', 'value' => 'admin@mamilo.com']);
    }

    /** @test */
    public function test_non_admin_cannot_update_settings()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/admin/settings', [
                'site_name' => 'Hacked Name'
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('settings', ['key' => 'site_name', 'value' => 'Hacked Name']);
    }
}
