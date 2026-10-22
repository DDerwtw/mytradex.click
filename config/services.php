<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'coingecko' => [
        'base_url' => env('COINGECKO_BASE_URL', 'https://api.coingecko.com/api/v3'),
        'demo_api_key' => env('COINGECKO_DEMO_API_KEY'),
    ],

    'tron' => [
        'base_url' => env('TRONGRID_BASE_URL', 'https://api.trongrid.io'),
        'api_key' => env('TRONGRID_API_KEY'),
        'deposit_address' => env('TRON_USDT_TRC20_DEPOSIT_ADDRESS'),
        'usdt_contract' => env('TRON_USDT_TRC20_CONTRACT', 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'),
        'deposit_sync_enabled' => env('TRON_DEPOSIT_SYNC_ENABLED', false),
        'investment_deposits_enabled' => env('TRON_INVESTMENT_DEPOSITS_ENABLED', false),
    ],

];
