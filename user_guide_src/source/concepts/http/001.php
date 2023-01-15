<?php

use CodeIgniter\HTTP\IncomingRequest;

$request = request();

// the URI path being requested (i.e., /about)
$request->getUri()->getPath();

// Retrieve $_GET and $_POST variables
$request->getGet('foo');
$request->getPost('foo');

// Retrieve from $_REQUEST which should include
// both $_GET and $_POST contents
$request->getVar('foo');

// Retrieve JSON from AJAX calls
$request->getJSON();

// Retrieve server variables
$request->getServer('Host');

// Retrieve an HTTP Request header, with case-insensitive names
$request->header('host');
$request->header('Content-Type');

// Checks the HTTP method
$request->is('get');
$request->is('post');
