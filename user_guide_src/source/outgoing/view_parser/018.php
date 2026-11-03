<?php

$template = 'Hello, {firstname} {lastname}';
$data     = [
    'title'     => 'Mr',
    'firstname' => 'John',
    'lastname'  => 'Doe',
];

return $parser->setData($data)->renderString($template);
// Result: Hello, John Doe
