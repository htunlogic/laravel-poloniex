<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Poloniex authentication
    |--------------------------------------------------------------------------
    |
    | Authentication key and secret for poloniex API.
    |
    */

    'auth' => [
        'key' => env('POLONIEX_KEY', ''),
        'secret' => env('POLONIEX_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Api URLS
    |--------------------------------------------------------------------------
    |
    | Urls for Poloniex public and trading API
    |
    */

    'urls' => [
        'trading' => 'https://poloniex.com/tradingApi',
        'public' => 'https://poloniex.com/public',
    ],

];