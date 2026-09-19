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
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL', 'http://localhost:8000') . '/google/callback'),
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
        'redirect' => env('META_REDIRECT_URI', env('APP_URL', 'http://localhost:8000') . '/meta/callback'),
        'graph_version' => env('META_GRAPH_VERSION', env('META_API_VERSION', 'v21.0')),
        'config_id' => env('META_CONFIG_ID'),
        'verify_token' => env('META_VERIFY_TOKEN'),
        'system_user_token' => env('META_SYSTEM_USER_TOKEN'),
        'use_fake' => env('META_USE_FAKE', false),
        // WhatsApp Cloud API
        'whatsapp_phone_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'whatsapp_token' => env('WHATSAPP_ACCESS_TOKEN', env('META_SYSTEM_USER_TOKEN')),
        'whatsapp_webhook_verify' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN', env('META_VERIFY_TOKEN')),
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
