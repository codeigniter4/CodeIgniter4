<?php

// Force ipv4 resolve
$client->request('GET', '/', ['force_ip_resolve' => 'v4']); // v4 or v6
