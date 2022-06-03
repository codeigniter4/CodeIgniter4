<?php

if (strpos($response->getHeader('content-type'), 'application/json') !== false) {
    $body = json_decode($body);
}
