<?php

$client->request('GET', '/', ['cert' => ['/path/server.pem', 'password']]);
