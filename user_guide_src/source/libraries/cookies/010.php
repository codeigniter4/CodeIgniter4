<?php

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;
use Config\Services;

// getting cookie in the current cookie collection
$store = new CookieStore([
    new Cookie('login_token'),
    new Cookie('remember_token'),
]);
$store->get('login_token');

// getting cookie in the current Response's cookie collection
cookies()->get('login_token');
Services::response()->getCookie('remember_token');

// using the cookie helper to get cookie from the Response's cookie collection
helper('cookie');
get_cookie('remember_token');
