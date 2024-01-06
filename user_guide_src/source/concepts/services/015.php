<?php

$options1 = [
    'baseURI' => 'http://example.com/api/v1/',
    'timeout' => 3,
];
$client1 = \Config\Services::curlrequest($options1);

$options2 = [
    'baseURI' => 'http://another.example.com/api/v2/',
    'timeout' => 10,
];
$client2 = \Config\Services::curlrequest($options2);
// $options2 does not work.
// $client2 is the exactly same instance as $client1.
