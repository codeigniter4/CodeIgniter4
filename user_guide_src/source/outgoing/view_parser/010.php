<?php

$template = '{location}{name} lives in {city} on {planet}{/location}.';

$data = [
    'name'     => 'George',
    'location' => ['city' => 'Red City', 'planet' => 'Mars'],
];

return $parser->setData($data)->renderString($template, ['cascadeData' => false]);
// Result: {name} lives in Red City on Mars.

// or

return $parser->setData($data)->renderString($template, ['cascadeData' => true]);
// Result: George lives in Red City on Mars.
