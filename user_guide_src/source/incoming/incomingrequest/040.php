<?php

// Checks HTTP methods. Returns boolean.
$request->is('get');
$request->is('post');
$request->is('put');
$request->is('delete');
$request->is('head');
$request->is('patch');
$request->is('options');

// Checks if it is an AJAX request. The same as `$request->isAJAX()`.
$request->is('ajax');

// Checks if it is a JSON request.
$request->is('json');
