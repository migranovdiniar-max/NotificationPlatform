<?php

return [
    'secret' => env('WEBHOOK_SECRET', 'dev-secret'),
    'timeout' => env('WEBHOOK_TIMEOUT', 5), // сек
    'connect_timeout' => env('WEBHOOK_CONNECT_TIMEOUT', 2), // сек
];
