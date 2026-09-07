<?php

namespace Tests\Architecture;

use Tests\TestCase;

class BackendBoundaryTest extends TestCase
{
    public function test_foundation_does_not_duplicate_supabase_or_add_a_second_auth_stack(): void
    {
        $composer = (string) file_get_contents(base_path('composer.json'));

        foreach ([
            'laravel/sanctum',
            'laravel/passport',
            'livewire/livewire',
            'inertiajs/inertia-laravel',
            'supabase-community',
        ] as $forbiddenPackage) {
            $this->assertStringNotContainsString($forbiddenPackage, $composer);
        }

        $this->assertFalse(is_dir(base_path('database/migrations')), 'Supabase owns domain migrations.');

        foreach ([
            'app/Models',
            'app/Repositories',
            'app/Domain',
            'resources/js',
        ] as $forbiddenFoundationDirectory) {
            $this->assertFalse(
                is_dir(base_path($forbiddenFoundationDirectory)),
                "Unexpected foundation layer: {$forbiddenFoundationDirectory}",
            );
        }
    }
}
