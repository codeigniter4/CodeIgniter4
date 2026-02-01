<?php

// Modify default DNS Cache Timeout
$client->request('GET', '/', ['dns_cache_timeout' => 360]); // seconds
