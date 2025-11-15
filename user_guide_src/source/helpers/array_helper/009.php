<?php

$arrayToFlatten = [
    'personal' => [
        'first_name' => 'john',
        'last_name'  => 'smith',
        'age'        => '26',
        'address'    => 'US',
    ],
    'other_details' => 'marines officer',
];

$flattened = array_flatten_with_dots($arrayToFlatten);
