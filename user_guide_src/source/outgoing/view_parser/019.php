<?php

$template = 'Hello, {firstname} {initials} {lastname}';
$data = [
    'title'     => 'Mr',
    'firstname' => 'John',
    'lastname'  => 'Doe',
];
echo $parser->setData($data)
            ->renderString($template);
// Result: Hello, John {initials} Doe
