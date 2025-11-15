<?php

if (str_contains($response->header('content-type'), 'application/json')) {
    $body = json_decode($body);
}
