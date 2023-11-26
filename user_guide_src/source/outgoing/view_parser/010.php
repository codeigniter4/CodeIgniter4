<?php

$template = '{locations}{name} lives in {city} on {planet}{/locations}.';

$data = [
    'name'      => 'George',
    'locations' => [
        ['city' => 'Red City', 'planet' => 'Mars'],
    ],
];

return $parser->setData($data)->renderString($template, ['cascadeData' => false]);
// Result: {name} lives in Red City on Mars.

// or

return $parser->setData($data)->renderString($template, ['cascadeData' => true]);
// Result: George lives in Red City on Mars.
