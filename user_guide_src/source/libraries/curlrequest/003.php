<?php

$options = [
    'baseURI' => 'http://example.com/api/v1/',
    'timeout' => 3,
];
$client = \Config\Services::curlrequest($options);
