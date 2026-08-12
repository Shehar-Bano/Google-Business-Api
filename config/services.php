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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'firebase' => [
        'credentials' => env('FIREBASE_CREDENTIALS'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'instagram' => [
        'client_id' => env('Instagram_app_ID', env('INSTAGRAM_APP_ID')),
        'client_secret' => env('Instagram_app_secret', env('INSTAGRAM_APP_SECRET')),
        'redirect' => env('INSTAGRAM_REDIRECT_URI', env('FACEBOOK_REDIRECT_URI')),
    ],

    'google' => [
        'client_id' => env('Google_Client_ID'),
        'client_secret' => env('Google_Client_Secret'),
        'redirect' => env('Google_Redirect_URI'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash-lite'),
    ],

    'openai' => [
        // Supports the project's legacy OPENAI/OpenAI name; prefer OPENAI_API_KEY for new setups.
        'api_key' => env('OPENAI_API_KEY') ?: (env('OPENAI') ?: env('OpenAI')),
        'model' => env('OPENAI_MODEL', 'gpt-5.6-sol'),
        'image_model' => env('OPENAI_IMAGE_MODEL', 'gpt-image-2'),
    ],

    'google_ads' => [
        'developer_token' => env('DEVELOPER_API_TOKEN'),
        'client_id' => env('Google_Client_ID'),
        'client_secret' => env('Google_Client_Secret'),
        'refresh_token' => env('REFRESH_TOKEN'),
        'customer_id' => env('Customer_ID', env('GOOGLE_ADS_CUSTOMER_ID')),
    ],

    'whatsapp' => [
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('PHONE_NO_ID'),
        'business_account_id' => env('WhatsApp_Business_Account_ID'),
        'provider' => env('WHATSAPP_PROVIDER', 'log'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'verify_sid' => env('TWILIO_VERIFY_SID'),
        'from' => env('TWILIO_PHONE_NUMBER'),
        'messaging_sid' => env('TWILIO_MESSAGING_SID'),
        'app_hash' => env('TWILIO_APP_HASH'),
    ],

    'dataforseo' => [
        'login' => env('DATAFORSEO_LOGIN', 'bshehar2002@gmail.com'),
        'password' => env('DATAFORSEO_PASSWORD', '933246dac8834240'),
    ],

];
