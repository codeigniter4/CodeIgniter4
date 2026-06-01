<?php

$uri = new \CodeIgniter\HTTP\URI('https://example.com/users?q=bob&page=1');

$page = $uri->withQuery('page=2');
// https://example.com/users?page=2

$filtered = $uri->withQueryArray([
    'q'    => 'alice',
    'role' => 'admin',
]);
// https://example.com/users?q=alice&role=admin
