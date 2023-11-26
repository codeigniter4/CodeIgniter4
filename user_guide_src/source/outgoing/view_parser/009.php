<?php

$template = '{name} lives in {locations}{city} on {planet}{/locations}.';

$data = [
    'name'      => 'George',
    'locations' => [
        ['city' => 'Red City', 'planet' => 'Mars'],
    ],
];

return $parser->setData($data)->renderString($template);
// Result: George lives in Red City on Mars.
