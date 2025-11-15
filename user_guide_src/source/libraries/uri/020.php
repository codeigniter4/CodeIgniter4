<?php

$uri = new \CodeIgniter\HTTP\URI('http://www.example.com?foo=bar&bar=baz&baz=foz');

// Returns 'foo=bar'
echo $uri->getQuery(['only' => ['foo']]);

// Returns 'foo=bar&baz=foz'
echo $uri->getQuery(['except' => ['bar']]);
