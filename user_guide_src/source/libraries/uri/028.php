<?php

$uri = new \CodeIgniter\HTTP\URI('https://example.com/users?q=bob&page=1');

$nextPage = $uri->withQueryVar('page', 2);
// https://example.com/users?q=bob&page=2

$withoutSearch = $uri->withQueryVar('q', null);
// https://example.com/users?page=1

$filtered = $uri->withQueryVars([
    'q'    => null,
    'page' => 1,
    'role' => 'admin',
]);
// https://example.com/users?page=1&role=admin
