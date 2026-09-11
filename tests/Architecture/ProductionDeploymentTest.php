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
        $this->assertStringNotContainsString('artisan migrate', $script);
        $this->assertStringNotContainsString('queue:', $script);
        $this->assertStringNotContainsString('DB_', $script);
        $this->assertStringNotContainsString('SUPABASE_SECRET_KEY', $script);
    }

    public function test_production_environment_example_is_minimal_and_safe(): void
    {
        $environment = (string) file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString('APP_ENV=production', $environment);
        $this->assertStringContainsString('APP_DEBUG=false', $environment);
        $this->assertStringContainsString('LOG_CHANNEL=stderr', $environment);
        $this->assertStringContainsString('SUPABASE_URL=', $environment);
        $this->assertStringContainsString('SUPABASE_PUBLISHABLE_KEY=', $environment);
        $this->assertStringNotContainsString('SUPABASE_SECRET_KEY', $environment);
        $this->assertStringNotContainsString('DB_CONNECTION', $environment);
        $this->assertStringNotContainsString('QUEUE_CONNECTION', $environment);
    }
}
