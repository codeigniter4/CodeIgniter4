<?php

$response = $client->request('GET', 'https://api.example.com/items', [
    'retry' => [
        'max_retries'         => 3,
        'delay'               => [100, 500, 1000],
        'max_delay'           => 5000,
        'status_codes'        => [429, 500, 502, 503, 504],
        'curl_errors'         => true,
        'respect_retry_after' => true,
    ],
]);
