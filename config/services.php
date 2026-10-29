<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // --- PUSH NOTIFIKASI (Firebase Cloud Messaging HTTP v1) ---
    // Push aktif otomatis begitu file kunci service account ada. Lihat docs/push-notifikasi.md.
    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID', 'stt-gpi-papua'),
        'credentials' => env('FCM_CREDENTIALS', storage_path('app/firebase/service-account.json')),
        // Web Push certificate (Firebase Console → Project settings → Cloud Messaging → Web configuration)
        'vapid_key' => env('FCM_VAPID_KEY'),
        'web' => [
            'apiKey' => env('FCM_WEB_API_KEY', 'AIzaSyCric2X1hFP4TJ4bghlSnavjQZwmnszehU'),
            'authDomain' => 'stt-gpi-papua.firebaseapp.com',
            'projectId' => env('FCM_PROJECT_ID', 'stt-gpi-papua'),
            'storageBucket' => 'stt-gpi-papua.firebasestorage.app',
            'messagingSenderId' => env('FCM_SENDER_ID', '633336694324'),
            'appId' => env('FCM_WEB_APP_ID', '1:633336694324:web:a2cb604ff59a4299b4ec61'),
        ],
    ],

    // --- KONFIGURASI BARU: GEMINI AI ---
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],

];