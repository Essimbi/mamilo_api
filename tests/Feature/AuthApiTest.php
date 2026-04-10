<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Authentication Endpoints Tests
 */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: POST /auth/login - Successful login
     */
    public function test_can_login_with_valid_credentials(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/v1/auth/login', $data);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['token'], 'message']);
    }

    /**
     * Test: POST /auth/login - Invalid password
     */
    public function test_cannot_login_with_invalid_password(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];

        // Act
        $response = $this->postJson('/api/v1/auth/login', $data);

        // Assert
        $response->assertStatus(401);
    }

    /**
     * Test: POST /auth/logout - Logout authenticated user
     */
    public function test_can_logout_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/auth/logout');

        // Assert
        $response->assertStatus(200);
    }

    /**
     * Test: GET /auth/me - Get authenticated user
     */
    public function test_can_get_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create(['name' => 'John Doe']);
        $token = $user->createToken('test-token')->plainTextToken;

        // Act
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/auth/me');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'John Doe');
    }

    /**
     * Test: GET /auth/me - Unauthenticated fails
     */
    public function test_cannot_get_user_without_authentication(): void
    {
        // Act
        $response = $this->getJson('/api/v1/auth/me');

        // Assert
        $response->assertStatus(401);
    }
}
