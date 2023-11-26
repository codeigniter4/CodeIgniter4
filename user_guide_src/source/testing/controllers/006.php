<?php

$request = new \CodeIgniter\HTTP\IncomingRequest(
    new \Config\App(),
    new \CodeIgniter\HTTP\URI('http://example.com'),
    null,
    new \CodeIgniter\HTTP\UserAgent()
);

$request->setLocale($locale);

$results = $this->withRequest($request)
    ->controller(\App\Controllers\ForumController::class)
    ->execute('showCategories');
