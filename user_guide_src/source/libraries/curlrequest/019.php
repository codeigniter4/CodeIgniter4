<?php

$client->request('get', '/', ['cert' => ['/path/server.pem', 'password']]);
