<?php

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;
use Config\Services;

// check if cookie is in the current cookie collection
$store = new CookieStore([
    new Cookie('login_token'),
    new Cookie('remember_token'),
]);
$store->has('login_token');

// check if cookie is in the current Response's cookie collection
cookies()->has('login_token');
Services::response()->hasCookie('remember_token');

// using the cookie helper to check the current Response
// not available to v4.1.1 and lower
helper('cookie');
has_cookie('login_token');
