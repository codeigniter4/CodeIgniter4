<?php

use CodeIgniter\Cookie\Cookie;
use Config\Services;
use DateTime;

$response = Services::response();

$cookie = new Cookie(
    'remember_token',
    'f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6',
    [
        'expires' => new DateTime('+2 hours'),
    ]
);

$response->setCookie($cookie);
