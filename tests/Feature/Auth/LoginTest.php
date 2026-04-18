<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login|test@example.com|127.0.0.1');
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@test.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'Test@Password123!',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_vendor_redirected_to_vendor_dashboard(): void
    {
        $user = User::factory()->vendor()->create([
            'email' => 'vendor@test.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'vendor@test.com',
            'password' => 'Test@Password123!',
        ]);

        $response->assertRedirect('/vendor/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@test.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->admin()->inactive()->create([
            'email' => 'inactive@test.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'Test@Password123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_login_throttle_after_max_attempts(): void
    {
        User::factory()->admin()->create([
            'email' => 'throttle@test.com',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'throttle@test.com',
                'password' => 'WrongPassword!',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'throttle@test.com',
            'password' => 'Test@Password123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_login_validation_requires_email(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'Test@Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_validation_requires_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
