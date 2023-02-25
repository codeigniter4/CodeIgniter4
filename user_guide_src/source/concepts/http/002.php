<?php

use CodeIgniter\HTTP\Response;

$response = response();

$response->setStatusCode(Response::HTTP_OK);
$response->setBody($output);
$response->setHeader('Content-Type', 'text/html');
$response->noCache();

// Sends the output to the browser
// This is typically handled by the framework
$response->send();
