<?php

namespace Tests\Architecture;

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
}
