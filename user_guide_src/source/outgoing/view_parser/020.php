<?php

$template = 'Hello, {firstname} {lastname} ({degrees}{degree} {/degrees})';
$data     = [
    'degrees'   => 'Mr',
    'firstname' => 'John',
    'lastname'  => 'Doe',
    'titles'    => [
        ['degree' => 'BSc'],
        ['degree' => 'PhD'],
    ],
];
echo $parser->setData($data)
    ->renderString($template);
// Result: Hello, John Doe (Mr{degree} {/degrees})
