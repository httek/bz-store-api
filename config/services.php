<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */
    '7ox' => [
        'storage' => [
            'host'          => env('7OX_FS_DN'),
            'bucket'        => env('7OX_FS_BK'),
            'access_key'    => env('7OX_FS_AK'),
            'secret_key'    => env('7OX_FS_SK'),
        ]
    ],

];
