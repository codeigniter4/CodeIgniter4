<?php

use CodeIgniter\Cookie\Cookie;
use DateTime;

// Using the constructor
$cookie = new Cookie(
    'remember_token',
    'f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6',
    [
        'expires'  => new DateTime('+2 hours'),
        'prefix'   => '__Secure-',
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,
        'httponly' => true,
        'raw'      => false,
        'samesite' => Cookie::SAMESITE_LAX,
    ]
);

// Supplying a Set-Cookie header string
$cookie = Cookie::fromHeaderString(
    'remember_token=f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6; Path=/; Secure; HttpOnly; SameSite=Lax',
    false, // raw
);

// Using the fluent builder interface
$cookie = (new Cookie('remember_token'))
    ->withValue('f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6')
    ->withPrefix('__Secure-')
    ->withExpires(new DateTime('+2 hours'))
    ->withPath('/')
    ->withDomain('')
    ->withSecure(true)
    ->withHTTPOnly(true)
    ->withSameSite(Cookie::SAMESITE_LAX);

// Using the global function `cookie` which implicitly calls `new Cookie()`
$cookie = cookie('remember_token', 'f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6');
