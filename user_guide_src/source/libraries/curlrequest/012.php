<?php

if (strpos($response->header('content-type'), 'application/json') !== false) {
    $body = json_decode($body);
}
