<?php

$client = \Config\Services::curlrequest([
    'baseURI' => 'https://example.com/api/v1/',
]);

// GET http:example.com/api/v1/photos
$client->get('photos');

// GET http:example.com/api/v1/photos/13
$client->delete('photos/13');
