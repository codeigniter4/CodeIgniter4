<?php

$client->request(
    'GET',
    'http://example.com',
    ['proxy' => 'http://localhost:3128']
);
