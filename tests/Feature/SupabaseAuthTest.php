<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupabaseAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.supabase.url', 'https://project.supabase.co');
        config()->set('services.supabase.publishable_key', 'sb_publishable_test');
        config()->set('services.supabase.secret_key', 'sb_secret_test');
    }

    public function test_me_rejects_missing_bearer_token(): void
    {
        $this->getJson('/api/me')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_me_rejects_invalid_supabase_token(): void
    {
        Http::fake([
            'https://project.supabase.co/auth/v1/user' => Http::response(['message' => 'invalid'], 401),
        ]);

        $this->withToken('invalid-user-jwt')
            ->getJson('/api/me')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_me_returns_only_minimal_identity_for_valid_user(): void
    {
        Http::fake([
            'https://project.supabase.co/auth/v1/user' => Http::response([
                'id' => 'user-123',
                'email' => 'user@example.com',
                'user_metadata' => ['ignored' => true],
            ], 200),
        ]);

        $this->withToken('valid-user-jwt')
            ->getJson('/api/me')
            ->assertOk()
            ->assertExactJson([
                'id' => 'user-123',
                'email' => 'user@example.com',
            ]);
    }

    public function test_me_returns_service_unavailable_when_supabase_auth_is_down(): void
    {
        Http::fake([
            'https://project.supabase.co/auth/v1/user' => Http::response(['message' => 'upstream'], 500),
        ]);

        $this->withToken('valid-user-jwt')
            ->getJson('/api/me')
            ->assertStatus(503)
            ->assertExactJson(['message' => 'Authentication service unavailable.']);
    }
}
