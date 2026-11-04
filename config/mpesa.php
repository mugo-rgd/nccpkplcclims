<?php

return [
    'environment' => env(
        'MPESA_ENVIRONMENT',
        'sandbox'
    ),

    'consumer_key' => env(
        'MPESA_CONSUMER_KEY'
    ),

    'consumer_secret' => env(
        'MPESA_CONSUMER_SECRET'
    ),

    'shortcode' => env(
        'MPESA_SHORTCODE'
    ),

    'passkey' => env(
        'MPESA_PASSKEY'
    ),

    'transaction_type' => env(
        'MPESA_TRANSACTION_TYPE',
        'CustomerPayBillOnline'
    ),

    'account_number' => env(
        'MPESA_ACCOUNT_NUMBER',
        'mclab'
    ),

    'callback_url' => env(
        'MPESA_CALLBACK_URL'
    ),

    'timeout' => (int) env(
        'MPESA_TIMEOUT',
        60
    ),

    'base_url' => env(
        'MPESA_ENVIRONMENT',
        'sandbox'
    ) === 'production'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke',
];
