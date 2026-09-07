<?php

namespace App\Http\Middleware;

use App\Services\Supabase\SupabaseClient;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class SupabaseUser
{
    public function __construct(private readonly SupabaseClient $supabase) {}

    public function handle(Request $request, Closure $next): Response
    {
        $jwt = $request->bearerToken();

        if (! is_string($jwt) || $jwt === '') {
            return $this->unauthorized();
        }

        try {
            $user = $this->supabase->getUser($jwt);
        } catch (RuntimeException) {
            return response()->json([
                'message' => 'Authentication service unavailable.',
            ], 503);
        }

        if ($user === null || ! isset($user['id'])) {
            return $this->unauthorized();
        }

        $request->attributes->set('supabase_user', $user);

        return $next($request);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json([
            'message' => 'Unauthenticated.',
        ], 401);
    }
}
