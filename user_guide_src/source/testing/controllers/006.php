<?php

$request = new \CodeIgniter\HTTP\IncomingRequest(new \Config\App(), new URI('http://example.com'));
$request->setLocale($locale);

$results = $this->withRequest($request)
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
