<?php

use CodeIgniter\Cookie\Cookie;
use DateTime;
use DateTimeZone;

$cookie = new Cookie(
    'remember_token',
    'f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6',
    [
        'expires'  => new DateTime('2025-02-14 00:00:00', new DateTimeZone('UTC')),
        'prefix'   => '__Secure-',
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,
        'httponly' => true,
        'raw'      => false,
        'samesite' => Cookie::SAMESITE_LAX,
    ]
);

$cookie->getName();             // 'remember_token'
$cookie->getPrefix();           // '__Secure-'
$cookie->getPrefixedName();     // '__Secure-remember_token'
$cookie->getExpiresTimestamp(); // Unix timestamp
$cookie->getExpiresString();    // 'Fri, 14-Feb-2025 00:00:00 GMT'
$cookie->isExpired();           // false
$cookie->getMaxAge();           // the difference from time() to expires
$cookie->isRaw();               // false
$cookie->isSecure();            // true
$cookie->getPath();             // '/'
$cookie->getDomain();           // ''
$cookie->isHTTPOnly();          // true
$cookie->getSameSite();         // 'Lax'

// additional getter
$cookie->getId(); // '__Secure-remember_token;;/'

// when using `setcookie()`'s alternative signature on PHP 7.3+
// you can easily use the `getOptions()` method to supply the
// $options parameter
$cookie->getOptions();
