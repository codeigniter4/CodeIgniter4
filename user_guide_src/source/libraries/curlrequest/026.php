<?php

$client->request('GET', '/status/500');
// If the response code is 500, an HTTPException is thrown,
// and a detailed error report is displayed if in development mode.

$response = $client->request('GET', '/status/500', ['http_errors' => false]);
echo $response->getStatusCode(); // 500
echo $response->getBody();       // You can see the response body.
