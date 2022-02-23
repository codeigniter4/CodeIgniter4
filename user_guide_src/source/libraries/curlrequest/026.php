<?php

$client->request('GET', '/status/500');
// Will fail verbosely

$res = $client->request('GET', '/status/500', ['http_errors' => false]);
echo $res->getStatusCode();
// 500
