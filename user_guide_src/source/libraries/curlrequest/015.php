<?php

$client->request('GET', 'http://example.com', ['allow_redirects' => [
    'max'       => 10,
    'protocols' => ['https'], // Force HTTPS domains only.
]]);
