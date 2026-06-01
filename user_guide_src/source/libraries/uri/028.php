<?php

$uri = new \CodeIgniter\HTTP\URI('https://example.com/users?q=bob&page=1');

$nextPage = $uri->withQueryVar('page', 2);
// https://example.com/users?q=bob&page=2

$filtered = $uri->withQueryVars([
    'q'    => 'alice',
    'page' => 1,
    'role' => 'admin',
]);
// https://example.com/users?q=alice&page=1&role=admin
