<?php

$supported = [
    'de',
    'en-US',
];

$lang = $request->negotiate('language', $supported);
// or
$lang = $negotiate->language($supported);
