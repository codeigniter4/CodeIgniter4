<?php

$client->request('GET', 'http://example.com', ['allow_redirects' => true]);
/*
 * Sets the following defaults:
 * 'max'       => 5, // Maximum number of redirects to follow before stopping
 * 'strict'    => true, // Ensure POST requests stay POST requests through redirects
 * 'protocols' => ['http', 'https'] // Restrict redirects to one or more protocols
 */
