<?php

$client = \Config\Services::curlrequest();

$response = $client->request('GET', 'https://api.github.com/user', [
    'auth' => ['user', 'pass'],
]);
