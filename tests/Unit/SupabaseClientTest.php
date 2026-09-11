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
}
