<?php

return [
    'default' => env('SMS_DRIVER', 'eucp'),

    'drivers' => [
        'eucp' => [
            'account' => env('EUCP_ACCOUNT'),
            'password' => env('EUCP_PASSWORD'),
            'session_key' => env('EUCP_SESSION_KEY'),
            'wsdl_address' => env('EUCP_WSDL_ADDRESS'),
        ],
        'jianzhou' => [
            'account' => env('JIANZHOU_ACCOUNT'),
            'password' => env('JIANZHOU_PASSWORD'),
            'wsdl_address' => env('JIANZHOU_WSDL_ADDRESS'),
        ],
        'xcm' => [
            'account' => env('XCM_ACCOUNT'),
            'password' => env('XCM_PASSWORD'),
            'cpid' => env('XCM_CPID'),
            'chid' => env('XCM_CHID'),
            'wsdl_address' => env('XCM_WSDL_ADDRESS'),
        ],
    ],
];