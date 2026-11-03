<?php

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;

$store = new CookieStore([
    new Cookie('login_token'),
    new Cookie('remember_token'),
]);

$store->dispatch(); // After dispatch, the collection is now empty.
