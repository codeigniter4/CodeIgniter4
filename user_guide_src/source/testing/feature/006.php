<?php

$headers = [
    'CONTENT_TYPE' => 'application/json',
];

$result = $this->withHeaders($headers)->post('users');
