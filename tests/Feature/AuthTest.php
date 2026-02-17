<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Insert roles for testing
        Role::insert([
            ['id' => 1, 'name' => 'User', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Researcher', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Healthcare Provider', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Administrator', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 1, // Assign a valid role
        ]);

        // Registration redirects to dashboard
        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role_id' => 1,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'password123'),
            'role_id' => 1, // Assign a role
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // Login redirects to OTP first
        $response->assertRedirect('/otp');
        $this->assertAuthenticatedAs($user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_reset_password()
    {
        $user = User::factory()->create([
            'role_id' => 1, // Assign a role
        ]);

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Password reset redirects to login
        $response->assertRedirect('/login');

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }
}