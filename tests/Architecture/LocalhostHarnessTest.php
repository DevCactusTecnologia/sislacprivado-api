<?php

namespace Tests\Architecture;

use Tests\TestCase;

class LocalhostHarnessTest extends TestCase
{
    public function test_local_environment_is_isolated_from_production_contract(): void
    {
        $path = base_path('.env.local.example');

        $this->assertFileExists($path);

        $environment = (string) file_get_contents($path);

        $this->assertStringContainsString('APP_ENV=local', $environment);
        $this->assertStringContainsString('APP_DEBUG=true', $environment);
        $this->assertStringContainsString('APP_URL=http://127.0.0.1:8000', $environment);
        $this->assertStringContainsString('SUPABASE_URL=', $environment);
        $this->assertStringContainsString('SUPABASE_PUBLISHABLE_KEY=', $environment);
        $this->assertStringNotContainsString('SUPABASE_SECRET_KEY', $environment);
        $this->assertStringNotContainsString('DB_CONNECTION', $environment);
    }

    public function test_local_start_script_never_runs_database_or_queue_operations(): void
    {
        $path = base_path('local/start.ps1');

        $this->assertFileExists($path);

        $script = (string) file_get_contents($path);

        $this->assertStringContainsString('php artisan serve', $script);
        $this->assertStringContainsString('127.0.0.1', $script);
        $this->assertStringContainsString('8000', $script);
        $this->assertStringContainsString('php artisan key:generate', $script);
        $this->assertStringNotContainsString('artisan migrate', $script);
        $this->assertStringNotContainsString('queue:', $script);
        $this->assertStringNotContainsString('SUPABASE_SECRET_KEY', $script);
    }

    public function test_local_smoke_covers_public_and_authenticated_paths(): void
    {
        $path = base_path('local/smoke.ps1');

        $this->assertFileExists($path);

        $script = (string) file_get_contents($path);

        $this->assertStringContainsString('/up', $script);
        $this->assertStringContainsString('/api/health', $script);
        $this->assertStringContainsString('/api/me', $script);
        $this->assertStringContainsString('401', $script);
        $this->assertStringContainsString('BearerToken', $script);
    }

    public function test_ci_validates_localhost_powershell_syntax(): void
    {
        $workflow = (string) file_get_contents(base_path('.github/workflows/ci.yml'));

        $this->assertStringContainsString('Validate localhost PowerShell syntax', $workflow);
        $this->assertStringContainsString('local/start.ps1', $workflow);
        $this->assertStringContainsString('local/smoke.ps1', $workflow);
    }
}
