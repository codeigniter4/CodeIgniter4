<?php

$supported = [
    'en',
    'de',
];

$lang = $request->negotiate('language', $supported);
// or
$lang = $negotiate->language($supported);
