<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_login(): void
    {
        // Register
        $resp = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $resp->assertStatus(302); // redirect after register
        $this->assertAuthenticated();

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // Login
        $resp = $this->post('/login', [
            'email' => 'new@example.com',
            'password' => 'password',
        ]);

        $resp->assertStatus(302);
        $this->assertAuthenticatedAs(User::where('email', 'new@example.com')->first());
    }
}
