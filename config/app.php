<?php
declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'ApexSMM Global Panel',
    'env' => getenv('APP_ENV') ?: 'production',
    'url' => getenv('APP_URL') ?: 'http://localhost:3000',
    'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
    'currency' => getenv('APP_CURRENCY') ?: 'USD',
    'currency_symbol' => getenv('APP_CURRENCY_SYMBOL') ?: '$',
    'session_lifetime' => 86400 * 7,
    'session_name' => 'apex_smm_session',
];
