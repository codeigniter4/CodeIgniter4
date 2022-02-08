<?php

$template = '{location}{name} lives in {city} on {planet}{/location}.';

$data = [
    'name'     => 'George',
    'location' => ['city' => 'Red City', 'planet' => 'Mars'],
];

echo $parser->setData($data)->renderString($template, ['cascadeData'=>false]);
// Result: {name} lives in Red City on Mars.

echo $parser->setData($data)->renderString($template, ['cascadeData'=>true]);
// Result: George lives in Red City on Mars.
