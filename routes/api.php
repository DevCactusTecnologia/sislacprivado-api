<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', static fn () => response()->json([
    'ok' => true,
    'service' => 'sislacprivado-api',
]));

Route::middleware('supabase.user')->get('/me', static function (Request $request) {
    /** @var array<string, mixed> $user */
    $user = $request->attributes->get('supabase_user', []);

    return response()->json([
        'id' => $user['id'] ?? null,
        'email' => $user['email'] ?? null,
    ]);
});
