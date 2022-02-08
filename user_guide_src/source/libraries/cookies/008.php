<?php

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;

// Passing an array of `Cookie` objects in the constructor
$store = new CookieStore([
    new Cookie('login_token'),
    new Cookie('remember_token'),
]);

// Passing an array of `Set-Cookie` header strings
$store = CookieStore::fromCookieHeaders([
    'remember_token=me; Path=/; SameSite=Lax',
    'login_token=admin; Path=/; SameSite=Lax',
]);

// using the global `cookies` function
$store = cookies([new Cookie('login_token')], false);

// retrieving the `CookieStore` instance saved in our current `Response` object
$store = cookies();
