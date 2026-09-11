<?php

namespace Tests\Architecture;

use Tests\TestCase;

class ProductionDeploymentTest extends TestCase
{
    public function test_production_deploy_is_stateless_and_optimized(): void
    {
        $path = base_path('deploy/deploy.sh');

        $this->assertFileExists($path);

        $script = (string) file_get_contents($path);

        $this->assertStringContainsString('composer install', $script);
        $this->assertStringContainsString('--no-dev', $script);
        $this->assertStringContainsString('--optimize-autoloader', $script);
        $this->assertStringContainsString('php artisan optimize', $script);
        $this->assertStringContainsString('APP_KEY', $script);
        $this->assertStringContainsString('APP_URL', $script);
        $this->assertStringContainsString('FILTER_VALIDATE_URL', $script);
        $this->assertStringContainsString('SUPABASE_URL', $script);
        $this->assertStringContainsString('sb_publishable_', $script);
        $this->assertStringNotContainsString('artisan migrate', $script);
        $this->assertStringNotContainsString('queue:', $script);
        $this->assertStringNotContainsString('DB_', $script);
        $this->assertStringNotContainsString('SUPABASE_SECRET_KEY', $script);
    }

    public function test_production_deploy_checks_runtime_tools_and_write_permissions(): void
    {
        $script = (string) file_get_contents(base_path('deploy/deploy.sh'));

        $this->assertStringContainsString('command -v curl', $script);
        $this->assertStringContainsString('storage', $script);
        $this->assertStringContainsString('bootstrap/cache', $script);
        $this->assertStringContainsString('-w', $script);
    }

    public function test_production_smoke_checks_cover_liveness_and_supabase_auth(): void
    {
        $path = base_path('deploy/smoke.sh');

        $this->assertFileExists($path);

        $script = (string) file_get_contents($path);

        $this->assertStringContainsString('/up', $script);
        $this->assertStringContainsString('/api/health', $script);
        $this->assertStringContainsString('/api/me', $script);
        $this->assertStringContainsString('401', $script);
        $this->assertStringContainsString('BEARER_TOKEN', $script);
        $this->assertStringContainsString('Authorization: Bearer', $script);
    }

    public function test_ci_validates_deployment_shell_syntax(): void
    {
        $workflow = (string) file_get_contents(base_path('.github/workflows/ci.yml'));

        $this->assertStringContainsString('bash -n deploy/deploy.sh deploy/smoke.sh', $workflow);
    }

    public function test_production_environment_example_is_minimal_and_safe(): void
    {
        $environment = (string) file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString('APP_ENV=production', $environment);
        $this->assertStringContainsString('APP_KEY=', $environment);
        $this->assertStringContainsString('APP_URL=', $environment);
        $this->assertStringContainsString('APP_DEBUG=false', $environment);
        $this->assertStringContainsString('LOG_CHANNEL=stderr', $environment);
        $this->assertStringContainsString('SUPABASE_URL=', $environment);
        $this->assertStringContainsString('SUPABASE_PUBLISHABLE_KEY=', $environment);
        $this->assertStringNotContainsString('APP_URL=http://localhost', $environment);
        $this->assertStringNotContainsString('SUPABASE_SECRET_KEY', $environment);
        $this->assertStringNotContainsString('DB_CONNECTION', $environment);
        $this->assertStringNotContainsString('QUEUE_CONNECTION', $environment);
    }
}
