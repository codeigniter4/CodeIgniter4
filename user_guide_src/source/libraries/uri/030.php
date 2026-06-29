<?php

$uri = new \CodeIgniter\HTTP\URI('https://example.com/users?q=bob&page=1&role=admin');

$withoutPage = $uri->withoutQueryVars('page');
// https://example.com/users?q=bob&role=admin

$onlyFilters = $uri->withOnlyQueryVars('q', 'role');
// https://example.com/users?q=bob&role=admin
