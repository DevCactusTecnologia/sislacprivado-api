<?php

namespace App\Services\Supabase;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use LogicException;
use RuntimeException;

final class SupabaseClient
{
    private readonly string $url;

    private readonly string $publishableKey;

    public function __construct()
    {
        $this->url = rtrim((string) config('services.supabase.url'), '/');
        $this->publishableKey = (string) config('services.supabase.publishable_key');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getUser(string $jwt): ?array
    {
        $response = $this->userRequest($jwt)->get('/auth/v1/user');

        if (in_array($response->status(), [401, 403], true)) {
            return null;
        }

        if (! $response->successful()) {
            throw new RuntimeException('Supabase Auth unavailable');
        }

        $user = $response->json();

        return is_array($user) ? $user : null;
    }

    public function userRequest(string $jwt): PendingRequest
    {
        if ($jwt === '') {
            throw new LogicException('Supabase user JWT is required');
        }

        return $this->request($this->publishableKey)->withToken($jwt);
    }

    private function request(string $apiKey): PendingRequest
    {
        if ($this->url === '' || $apiKey === '') {
            throw new LogicException('Supabase API configuration is incomplete');
        }

        return Http::baseUrl($this->url)
            ->acceptJson()
            ->withHeaders(['apikey' => $apiKey])
            ->connectTimeout(5)
            ->timeout(10);
    }
}
