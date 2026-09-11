<?php

namespace Tests\Architecture;

use App\Services\Supabase\SupabaseClient;
use Tests\TestCase;

class SupabaseContractManifestTest extends TestCase
{
    public function test_manifest_tracks_only_runtime_supabase_contracts(): void
    {
        $path = base_path('contracts/supabase.php');

        $this->assertFileExists($path);

        /** @var array<string, mixed> $manifest */
        $manifest = require $path;

        $this->assertSame('supabase', $manifest['source_of_truth'] ?? null);
        $this->assertSame('http', $manifest['transport'] ?? null);
        $this->assertSame([
            [
                'service' => 'auth',
                'method' => 'GET',
                'path' => '/auth/v1/user',
                'authorization' => 'publishable_key_plus_user_jwt',
            ],
        ], $manifest['contracts'] ?? null);
        $this->assertArrayNotHasKey('database', $manifest);
        $this->assertArrayNotHasKey('tenant_database', $manifest);
    }

    public function test_foundation_has_no_unused_supabase_admin_credential_surface(): void
    {
        $services = (string) file_get_contents(config_path('services.php'));
        $environment = (string) file_get_contents(base_path('.env.example'));

        $this->assertFalse(method_exists(SupabaseClient::class, 'adminRequest'));
        $this->assertStringNotContainsString('secret_key', $services);
        $this->assertStringNotContainsString('SUPABASE_SECRET_KEY', $environment);
    }
}
