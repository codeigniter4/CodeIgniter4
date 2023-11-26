<?php

use CodeIgniter\Cookie\Cookie;
use Config\Cookie as CookieConfig;

// pass in a Config\Cookie instance before constructing a Cookie class
Cookie::setDefaults(new CookieConfig());
$cookie = new Cookie('login_token');

// pass in an array of defaults
$myDefaults = [
    'expires'  => 0,
    'samesite' => Cookie::SAMESITE_STRICT,
];
Cookie::setDefaults($myDefaults);
$cookie = new Cookie('login_token');
