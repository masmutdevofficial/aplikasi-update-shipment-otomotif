<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;
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

        $response = $this->postWithCsrf('/login', [
            'email' => 'admin@test.com',
            'password' => 'Test@Password123!',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_same_account_can_have_multiple_active_sessions(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'multi-session@test.com',
        ]);

        $sessionCookie = config('session.cookie');

        $firstLoginResponse = $this->postWithCsrf('/login', [
            'email' => 'multi-session@test.com',
            'password' => 'Test@Password123!',
        ], [
            $sessionCookie => 'browser-session-a',
        ]);

        $secondLoginResponse = $this->postWithCsrf('/login', [
            'email' => 'multi-session@test.com',
            'password' => 'Test@Password123!',
        ], [
            $sessionCookie => 'browser-session-b',
        ]);

        $firstLoginResponse->assertRedirect('/admin/dashboard');
        $secondLoginResponse->assertRedirect('/admin/dashboard');

        $firstSessionId = collect($firstLoginResponse->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === $sessionCookie)?->getValue();
        $secondSessionId = collect($secondLoginResponse->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === $sessionCookie)?->getValue();

        $this->assertNotEmpty($firstSessionId);
        $this->assertNotEmpty($secondSessionId);
        $this->assertNotSame($firstSessionId, $secondSessionId);

        $this->withCookie($sessionCookie, $firstSessionId)
            ->get('/admin/dashboard')
            ->assertOk();

        $this->withCookie($sessionCookie, $secondSessionId)
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_vendor_redirected_to_vendor_dashboard(): void
    {
        $user = User::factory()->vendor()->create([
            'email' => 'vendor@test.com',
        ]);

        $response = $this->postWithCsrf('/login', [
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

        $response = $this->postWithCsrf('/login', [
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

        $response = $this->postWithCsrf('/login', [
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
            $this->postWithCsrf('/login', [
                'email' => 'throttle@test.com',
                'password' => 'WrongPassword!',
            ]);
        }

        $response = $this->postWithCsrf('/login', [
            'email' => 'throttle@test.com',
            'password' => 'Test@Password123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_login_validation_requires_email(): void
    {
        $response = $this->postWithCsrf('/login', [
            'email' => '',
            'password' => 'Test@Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_validation_requires_password(): void
    {
        $response = $this->postWithCsrf('/login', [
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

        $token = 'logout-test-token';

        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->post('/logout', ['_token' => $token]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    protected function postWithCsrf(string $uri, array $data = [], array $cookies = []): TestResponse
    {
        $token = 'test-csrf-token';

        $request = $this->withSession(['_token' => $token]);

        foreach ($cookies as $name => $value) {
            $request = $request->withCookie($name, $value);
        }

        return $request->post($uri, [
            ...$data,
            '_token' => $token,
        ]);
    }
}
