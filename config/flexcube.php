<?php


return [
    'branch' => env('FCC_BRANCH', '000'),
    'source' => env('FCC_SOURCE'),
    'ubscamp' => env('FCC_UBSCAMP', 'FCUBS'),
    'endpoint' => env('FCC_ENDPOINT'),
    'user_id' => env('FCC_USERID', 'ESBPORTAL'),
    'reference_salt' => env('FCC_REFERENCE_SALT', 1000000000),

    'bi_reports' => [
        'endpoit' => env('FCC_BI_REPORTS_ENDPOINT'),
        'user_id' => env('FCC_BI_REPORTS_USER_ID'),
        'password' => env('FCC_BI_REPORTS_PASSWORD'),
        'services' => [
            'public_report' => env('FCC_BI_REPORTS_PUBLIC_REPORT', 'PublicReportService'),
            'public_report_path' => env('FCC_BI_REPORTS_PUBLIC_REPORT_PATH', '/Internal Reports/Reports/Customer Account Statement.xdo'),
            'customers_report_path' => env('FCC_BI_REPORTS_CUSTOMERS_REPORT_PATH', '/Internal Reports/Reports/Static Data.xdo')
        ],
    ],
    'log_channel' => env('FCC_LOG_CHANNEL', 'daily'),
];
