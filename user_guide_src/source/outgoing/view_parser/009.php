<?php

$template = '{name} lives in {location}{city} on {planet}{/location}.';

$data = [
    'name'     => 'George',
    'location' => ['city' => 'Red City', 'planet' => 'Mars'],
];

return $parser->setData($data)->renderString($template);
// Result: George lives in Red City on Mars.
