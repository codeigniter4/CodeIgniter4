<?php

$response = \Config\Services::response()
    ->redirect('https://example.com/path')
    ->setHeader('Some', 'header')
    ->setCookie('and', 'cookie');

throw new \CodeIgniter\HTTP\Exceptions\RedirectException($response);
