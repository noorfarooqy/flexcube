<?php


return [
    'branch' => env('FCC_BRANCH', '000'),
    'source' => env('FCC_SOURCE'),
    'ubscamp' => env('FCC_UBSCAMP', 'FCUBS'),
    'endpoint' =>  env('FCC_ENDPOINT'),
    'user_id' => env('FCC_USERID', 'ESBPORTAL'),
    'reference_salt' => env('FCC_REFERENCE_SALT', 1000000000),
];
