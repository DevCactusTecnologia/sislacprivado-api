<?php

return [
    'source_of_truth' => 'supabase',
    'transport' => 'http',
    'contracts' => [
        [
            'service' => 'auth',
            'method' => 'GET',
            'path' => '/auth/v1/user',
            'authorization' => 'publishable_key_plus_user_jwt',
        ],
    ],
];
