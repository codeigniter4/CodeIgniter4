<?php

$cookie = [
    'name'     => 'The Cookie Name',
    'value'    => 'The Value',
    'expire'   => '86500',
    'domain'   => '.some-domain.com',
    'path'     => '/',
    'prefix'   => 'myprefix_',
    'secure'   => true,
    'httponly' => false,
    'samesite' => 'Lax',
];

$response->setCookie($cookie);
