<?php

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;
use Config\Services;

$store = new CookieStore([
    new Cookie('login_token'),
    new Cookie('remember_token'),
]);

// adding a new Cookie instance
$new = $store->put(new Cookie('admin_token', 'yes'));

// removing a Cookie instance
$new = $store->remove('login_token');
