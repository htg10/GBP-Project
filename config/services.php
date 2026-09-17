<?php

return [
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),
    ],
    'razorpay' => [
        'key' => env('RAZORPAY_KEY_ID'),
        'secret' => env('RAZORPAY_KEY_SECRET'),
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        // Separate API key (not OAuth) for the Places API — powers the real
        // Local Rank Checker. Optional: without it, rank checking falls back
        // to an AI-estimated result.
        'places_key' => env('GOOGLE_PLACES_API_KEY'),
        // Scopes needed for Business Profile (reviews, posts, media) + basic profile.
        'scopes' => [
            'https://www.googleapis.com/auth/business.manage',
            'openid',
            'email',
            'profile',
        ],
    ],

    'google_login' => [
        'client_id' => env('GOOGLE_LOGIN_CLIENT_ID', env('GOOGLE_CLIENT_ID')),
        'client_secret' => env('GOOGLE_LOGIN_CLIENT_SECRET', env('GOOGLE_CLIENT_SECRET')),
        'redirect' => env('GOOGLE_LOGIN_REDIRECT_URI', env('APP_URL', 'http://127.0.0.1:8099') . '/auth/google/callback'),
    ],

    'meta' => [
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'redirect' => env('META_REDIRECT_URI'),
        'graph_version' => env('META_GRAPH_VERSION', 'v21.0'),
        // Scopes needed to list Pages, publish to a Page feed, and publish to
        // the Page's linked Instagram Business Account.
        'scopes' => [
            'pages_show_list',
            'pages_manage_posts',
            'pages_read_engagement',
            'instagram_basic',
            'instagram_content_publish',
            'business_management',
        ],
    ],
];
