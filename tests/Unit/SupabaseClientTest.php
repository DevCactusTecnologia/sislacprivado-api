<?php

namespace Tests\Unit;

use App\Services\Supabase\SupabaseClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupabaseClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.supabase.url', 'https://project.supabase.co');
        config()->set('services.supabase.publishable_key', 'sb_publishable_test');
        config()->set('services.supabase.secret_key', 'sb_secret_test');
    }

    public function test_get_user_sends_publishable_key_and_user_jwt_in_separate_headers(): void
    {
        Http::fake([
            'https://project.supabase.co/auth/v1/user' => Http::response([
                'id' => 'user-123',
                'email' => 'user@example.com',
            ]),
        ]);

        $user = app(SupabaseClient::class)->getUser('user-jwt');

        $this->assertSame('user-123', $user['id'] ?? null);

        Http::assertSent(static function (Request $request): bool {
            return $request->url() === 'https://project.supabase.co/auth/v1/user'
                && $request->hasHeader('apikey', 'sb_publishable_test')
                && $request->hasHeader('Authorization', 'Bearer user-jwt');
        });
    }

    public function test_admin_request_uses_secret_key_only_as_apikey(): void
    {
        Http::fake([
            'https://project.supabase.co/rest/v1/lab_config*' => Http::response([]),
        ]);

        app(SupabaseClient::class)
            ->adminRequest()
            ->get('/rest/v1/lab_config?select=id');

        Http::assertSent(static function (Request $request): bool {
            return $request->hasHeader('apikey', 'sb_secret_test')
                && ! $request->hasHeader('Authorization');
        });
    }
}
